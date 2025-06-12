import React, { useState, useEffect, useCallback } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { toast } from "sonner";
import brain from "brain";
import { WordpressManagerConnectionsList, WordpressManagerConnectionValidate } from "types"; // Assuming these types are generated
import { CheckCircle2, XCircle, AlertTriangle, RefreshCw, ListCollapse } from "lucide-react";

// We will reuse parts of the Dashboard's layout or create a more generic AppLayout component later if needed.
// For now, this will be a simple page structure.

interface Connection extends WordpressManagerConnectionsList {}

export default function SettingsPage() {
  const [connections, setConnections] = useState<Connection[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // State for Add Connection Form
  const [newSiteUrl, setNewSiteUrl] = useState("");
  const [newUsername, setNewUsername] = useState("");
  const [newAppPassword, setNewAppPassword] = useState("");
  const [isAdding, setIsAdding] = useState(false);

  const fetchConnections = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    try {
      const response = await brain.list_wordpress_connections();
      const data = await response.json();
      if (response.ok) {
        setConnections(data);
      } else {
        const errorData = data as { detail?: string };
        throw new Error(errorData.detail || "Failed to fetch connections");
      }
    } catch (err: any) {
      console.error("Fetch connections error:", err);
      setError(err.message || "An unexpected error occurred while fetching connections.");
      toast.error("Failed to load connections", { description: err.message });
    }
    setIsLoading(false);
  }, []);

  useEffect(() => {
    fetchConnections();
  }, [fetchConnections]);

  const handleValidateConnection = async (connectionId: string) => {
    toast("Validating connection...", { id: `validate-${connectionId}` });
    try {
      const response = await brain.validate_wordpress_connection({ connectionId });
      const validatedConnection = await response.json();
      if (response.ok) {
        toast.success("Connection validated successfully!", {
          id: `validate-${connectionId}`,
          description: `Site: ${validatedConnection.site_url}`,
        });
        // Update the specific connection in the list
        setConnections(prevConnections => 
          prevConnections.map(conn => conn.id === connectionId ? { ...conn, ...validatedConnection, application_password: "********" } : conn)
        );
      } else {
        const errorData = validatedConnection as { detail?: string };
        throw new Error(errorData.detail || "Validation failed from server");
      }
    } catch (err: any) {
      console.error("Validate connection error:", err);
      toast.error("Validation Failed", {
        id: `validate-${connectionId}`,
        description: err.message || "Could not validate the connection.",
      });
    }
  };

  const handleAddConnection = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsAdding(true);
    toast("Adding connection...", { id: "add-connection" });
    try {
      const response = await brain.add_wordpress_connection({
        site_url: newSiteUrl,
        username: newUsername,
        application_password: newAppPassword,
      });
      const newConnection = await response.json();
      if (response.ok) {
        toast.success("WordPress connection added!", {
          id: "add-connection",
          description: `Site: ${newConnection.site_url}`,
        });
        setNewSiteUrl("");
        setNewUsername("");
        setNewAppPassword("");
        fetchConnections(); // Refresh the list of connections
      } else {
        const errorData = newConnection as { detail?: string };
        throw new Error(errorData.detail || "Failed to add connection");
      }
    } catch (err: any) {
      console.error("Add connection error:", err);
      toast.error("Failed to Add Connection", {
        id: "add-connection",
        description: err.message || "Could not save the connection.",
      });
    }
    setIsAdding(false);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white p-8 md:p-10">
      <header className="mb-10 pb-5 border-b border-gray-700/80 flex justify-between items-center">
        <h2 className="text-3xl md:text-4xl font-bold text-gray-100 tracking-tight">
          Settings
        </h2>
        <Button onClick={fetchConnections} variant="outline" className="border-gray-600 text-gray-300 hover:bg-gray-700/70 hover:text-white">
          <RefreshCw className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
          Refresh Connections
        </Button>
      </header>

      <div className="space-y-8">
        <Card className="bg-gray-800/70 border-gray-700/70 shadow-lg">
          <CardHeader>
            <CardTitle className="text-xl font-semibold text-gray-200">
              WordPress Site Connections
            </CardTitle>
          </CardHeader>
          <CardContent>
            {isLoading && <p className="text-gray-400 flex items-center"><RefreshCw className="mr-2 h-5 w-5 animate-spin" />Loading connections...</p>}
            {error && <p className="text-red-400 flex items-center"><AlertTriangle className="mr-2 h-5 w-5" />Error: {error}</p>}
            {!isLoading && !error && connections.length === 0 && (
              <p className="text-gray-500">
                No WordPress connections found. Add your first connection below.
              </p>
            )}
            {!isLoading && !error && connections.length > 0 && (
              <div className="space-y-4">
                {connections.map((conn) => (
                  <Card key={conn.id} className="bg-gray-700/50 border-gray-600 p-4">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                      <div className="mb-3 sm:mb-0">
                        <h3 className="text-lg font-semibold text-blue-300 break-all">{conn.site_url}</h3>
                        <p className="text-sm text-gray-400">Username: {conn.username}</p>
                        <p className="text-xs text-gray-500 mt-1">
                          Last Validated: {conn.last_validated_at ? new Date(conn.last_validated_at).toLocaleString() : "Never"}
                        </p>
                      </div>
                      <div className="flex items-center space-x-3 flex-shrink-0">
                        {conn.is_validated ? (
                          <Badge variant="default" className="bg-green-600 hover:bg-green-700 text-white">
                            <CheckCircle2 className="mr-1 h-4 w-4" /> Validated
                          </Badge>
                        ) : (
                          <Badge variant="destructive" className="bg-red-600 hover:bg-red-700 text-white">
                            <XCircle className="mr-1 h-4 w-4" /> Not Validated
                          </Badge>
                        )}
                        <Button 
                          size="sm" 
                          variant="outline"
                          className="border-blue-500 text-blue-400 hover:bg-blue-500/20 hover:text-blue-300"
                          onClick={() => handleValidateConnection(conn.id)}
                        >
                          <RefreshCw className="mr-2 h-4 w-4" /> Validate
                        </Button>
                      </div>
                    </div>
                  </Card>
                ))}
              </div>
            )}

            {/* Add Connection Form Placeholder - to be implemented next */}
            <div className="mt-8 pt-6 border-t border-gray-700/50">
              <h3 className="text-lg font-medium text-gray-200 mb-4">Add New Connection</h3>
              <form onSubmit={handleAddConnection} className="space-y-4">
                <div>
                  <Label htmlFor="siteUrl" className="text-gray-300">Site URL (e.g., https://example.com)</Label>
                  <Input 
                    id="siteUrl" 
                    type="url" 
                    value={newSiteUrl} 
                    onChange={(e) => setNewSiteUrl(e.target.value)} 
                    placeholder="https://your-wordpress-site.com"
                    className="bg-gray-700 border-gray-600 text-white focus:border-blue-500"
                    required 
                  />
                </div>
                <div>
                  <Label htmlFor="username" className="text-gray-300">WordPress Username</Label>
                  <Input 
                    id="username" 
                    value={newUsername} 
                    onChange={(e) => setNewUsername(e.target.value)} 
                    placeholder="your_wp_username"
                    className="bg-gray-700 border-gray-600 text-white focus:border-blue-500"
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="appPassword" className="text-gray-300">WordPress Application Password</Label>
                  <Input 
                    id="appPassword" 
                    type="password" 
                    value={newAppPassword} 
                    onChange={(e) => setNewAppPassword(e.target.value)} 
                    placeholder="xxxx xxxx xxxx xxxx xxxx xxxx"
                    className="bg-gray-700 border-gray-600 text-white focus:border-blue-500"
                    required
                  />
                   <p className="text-xs text-gray-500 mt-1">You can generate an Application Password in your WordPress admin area under Users &gt; Profile.</p>
                </div>
                <Button type="submit" className="bg-blue-600 hover:bg-blue-700 text-white" disabled={isAdding}>
                  {isAdding ? <><RefreshCw className="mr-2 h-4 w-4 animate-spin" /> Adding...</> : "Add Connection"}
                </Button>
              </form>
            </div>

          </CardContent>
        </Card>

        {/* Placeholder for other settings sections */}
        <Card className="bg-gray-800/70 border-gray-700/70 shadow-lg">
          <CardHeader>
            <CardTitle className="text-xl font-semibold text-gray-200">
              Account Settings
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-400">
              Account-related settings will be available here.
            </p>
             <div className="mt-6 p-6 border border-dashed border-gray-600 rounded-md text-center">
                <p className="text-gray-500">Account settings UI coming soon.</p>
            </div>
          </CardContent>
        </Card>

      </div>
    </div>
  );
}
