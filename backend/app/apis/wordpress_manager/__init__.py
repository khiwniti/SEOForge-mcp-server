from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel, HttpUrl
import databutton as db
import uuid
from typing import List, Optional

# Initialize router
router = APIRouter(
    prefix="/wordpress-manager",
    tags=["WordPress Management"],
)

# --- Pydantic Models ---
class WordPressConnectionBase(BaseModel):
    site_url: HttpUrl
    username: str
    # Application password should be stored, not the user's main password
    application_password: str # This will be stored encrypted or in a secure way

class WordPressConnectionCreate(WordPressConnectionBase):
    pass

class WordPressConnection(WordPressConnectionBase):
    id: str
    # We might add other fields like is_validated, last_validated_at later
    is_validated: bool = False
    last_validated_at: Optional[str] = None

class WordPressConnectionStored(BaseModel):
    id: str
    site_url: str # Storing as str as HttpUrl can be tricky with db.storage.json
    username: str
    # In a real scenario, this password should be encrypted before storing
    # For now, we'll store it directly but acknowledge this limitation.
    encrypted_application_password: str # Placeholder for actual encryption
    is_validated: bool = False
    last_validated_at: Optional[str] = None

# --- Storage Key ---
STORAGE_KEY_CONNECTIONS = "wordpress_connections_list_v1"

# --- Helper Functions ---
def get_connections_from_storage() -> List[WordPressConnectionStored]:
    """Fetches the list of WordPress connections from db.storage.json."""
    connections = db.storage.json.get(STORAGE_KEY_CONNECTIONS, default=[])
    # Ensure all items are dictionaries before trying to create Pydantic models
    return [WordPressConnectionStored(**conn) for conn in connections if isinstance(conn, dict)]

def save_connections_to_storage(connections: List[WordPressConnectionStored]):
    """Saves the list of WordPress connections to db.storage.json."""
    db.storage.json.put(STORAGE_KEY_CONNECTIONS, [conn.model_dump() for conn in connections])

# --- API Endpoints ---
@router.post("/connections", response_model=WordPressConnection)
async def add_wordpress_connection(connection_data: WordPressConnectionCreate):
    """Adds a new WordPress site connection."""
    connections = get_connections_from_storage()

    # Check for duplicates by site_url and username to avoid redundant entries
    for existing_conn in connections:
        if existing_conn.site_url == str(connection_data.site_url) and existing_conn.username == connection_data.username:
            raise HTTPException(status_code=409, detail="Connection with this Site URL and Username already exists.")

    new_id = str(uuid.uuid4())
    
    # TODO: Implement actual encryption for application_password before storing
    # For now, storing it as is or with a placeholder prefix
    placeholder_encrypted_password = f"encrypted_{connection_data.application_password}"

    new_connection_stored = WordPressConnectionStored(
        id=new_id,
        site_url=str(connection_data.site_url),
        username=connection_data.username,
        encrypted_application_password=placeholder_encrypted_password, # Store the "encrypted" version
        is_validated=False,
        last_validated_at=None
    )
    connections.append(new_connection_stored)
    save_connections_to_storage(connections)
    
    print(f"Added new WordPress connection: {new_connection_stored.id} for site {new_connection_stored.site_url}")

    # Return the public-facing model, not showing the encrypted password directly
    return WordPressConnection(
        id=new_connection_stored.id,
        site_url=connection_data.site_url, # Return original HttpUrl
        username=new_connection_stored.username,
        application_password="********", # Mask password in response
        is_validated=new_connection_stored.is_validated,
        last_validated_at=new_connection_stored.last_validated_at
    )

@router.get("/connections", response_model=List[WordPressConnection])
async def list_wordpress_connections():
    """Lists all stored WordPress site connections."""
    stored_connections = get_connections_from_storage()
    
    # Convert stored model to the public-facing response model
    response_connections = []
    for conn in stored_connections:
        response_connections.append(
            WordPressConnection(
                id=conn.id,
                site_url=HttpUrl(conn.site_url), # Convert back to HttpUrl
                username=conn.username,
                application_password="********", # Mask password
                is_validated=conn.is_validated,
                last_validated_at=conn.last_validated_at
            )
        )
    return response_connections

import requests
from requests.auth import HTTPBasicAuth
from datetime import datetime, timezone

# TODO: Add PUT /connections/{connection_id} (to update)
# TODO: Add DELETE /connections/{connection_id} (to remove)

@router.post("/connections/{connection_id}/validate", response_model=WordPressConnection)
async def validate_wordpress_connection(connection_id: str):
    """Validates a WordPress site connection by attempting to access its REST API."""
    connections = get_connections_from_storage()
    connection_to_validate_stored = None
    connection_index = -1

    for i, conn in enumerate(connections):
        if conn.id == connection_id:
            connection_to_validate_stored = conn
            connection_index = i
            break

    if not connection_to_validate_stored:
        raise HTTPException(status_code=404, detail="WordPress connection not found.")

    # Retrieve the "encrypted" password and remove the placeholder prefix for validation
    # In a real scenario, this would involve decryption.
    if connection_to_validate_stored.encrypted_application_password.startswith("encrypted_"):
        app_password = connection_to_validate_stored.encrypted_application_password[len("encrypted_"):]
    else:
        # Fallback or error if password format is unexpected
        # For now, assume it's the raw password if no prefix (should not happen with current add logic)
        app_password = connection_to_validate_stored.encrypted_application_password 

    site_url_str = str(connection_to_validate_stored.site_url)
    # Ensure the URL ends with a slash for proper joining with /wp-json/
    if not site_url_str.endswith('/'):
        site_url_str += '/'
    
    # Target a common REST API endpoint that requires authentication
    # /wp-json/wp/v2/users/me is a good choice as it returns the authenticated user's details
    validate_url = f"{site_url_str}wp-json/wp/v2/users/me"

    validation_successful = False
    error_message = None

    try:
        print(f"Attempting to validate WordPress connection ID: {connection_id} at URL: {validate_url}")
        response = requests.get(
            validate_url,
            auth=HTTPBasicAuth(connection_to_validate_stored.username, app_password),
            timeout=10 # Set a timeout for the request
        )
        response.raise_for_status() # Raises an HTTPError for bad responses (4XX or 5XX)
        
        # If we get here, the request was successful (status code 200-299)
        # We can add more checks here, e.g., checking if the response JSON contains expected fields
        # For now, a successful status code is enough for basic validation.
        user_data = response.json()
        if user_data.get("id"):
            validation_successful = True
            print(f"Validation successful for {connection_id}. User ID: {user_data.get('id')}, Name: {user_data.get('name')}")
        else:
            error_message = "Authenticated, but response format unexpected."
            print(f"Validation failed for {connection_id}: {error_message} - Response: {user_data}")

    except requests.exceptions.HTTPError as e:
        if e.response.status_code == 401:
            error_message = "Authentication failed. Check username or application password."
        elif e.response.status_code == 403:
            error_message = "Forbidden. The user may not have permission or REST API might be restricted."
        elif e.response.status_code == 404:
            error_message = f"REST API endpoint not found at {validate_url}. Check Site URL or permalinks."
        else:
            error_message = f"HTTP error: {e.response.status_code} - {e.response.reason}"
        print(f"Validation HTTPError for {connection_id}: {error_message} - Details: {e}")
    except requests.exceptions.ConnectionError as e:
        error_message = "Connection error. Check site URL or network connectivity."
        print(f"Validation ConnectionError for {connection_id}: {error_message} - Details: {e}")
    except requests.exceptions.Timeout:
        error_message = "Connection timed out. The WordPress site may be slow or unresponsive."
        print(f"Validation Timeout for {connection_id}: {error_message}")
    except requests.exceptions.RequestException as e:
        error_message = f"An unexpected error occurred during validation: {str(e)}"
        print(f"Validation RequestException for {connection_id}: {error_message} - Details: {e}")
    except Exception as e:
        error_message = f"A general error occurred: {str(e)}"
        print(f"Validation General Error for {connection_id}: {error_message} - Details: {e}")

    # Update connection status in storage
    connection_to_validate_stored.is_validated = validation_successful
    connection_to_validate_stored.last_validated_at = datetime.now(timezone.utc).isoformat()
    connections[connection_index] = connection_to_validate_stored
    save_connections_to_storage(connections)

    if not validation_successful:
        # If validation failed, we still return the connection object but with is_validated=False
        # The frontend can use the error_message if we decide to pass it back.
        # For now, just raising an HTTP exception to indicate failure to the client clearly.
        final_error = f"Validation failed for {connection_to_validate_stored.site_url}. Reason: {error_message or 'Unknown error'}"
        raise HTTPException(status_code=400, detail=final_error)

    # Return the public-facing model
    return WordPressConnection(
        id=connection_to_validate_stored.id,
        site_url=HttpUrl(connection_to_validate_stored.site_url),
        username=connection_to_validate_stored.username,
        application_password="********", # Mask password
        is_validated=connection_to_validate_stored.is_validated,
        last_validated_at=connection_to_validate_stored.last_validated_at
    )



