/** BlogContentResponse */
export interface BlogContentResponse {
  /**
   * Generated Text
   * The AI-generated blog post content.
   */
  generated_text: string;
}

/** BlogTopicRequest */
export interface BlogTopicRequest {
  /**
   * Topic
   * The main topic or title for the blog post.
   */
  topic: string;
  /**
   * Keywords
   * A list of keywords to focus on for SEO.
   * @example ["AI content","SEO optimization","blogging tools"]
   */
  keywords?: string[] | null;
}

/** HTTPValidationError */
export interface HTTPValidationError {
  /** Detail */
  detail?: ValidationError[];
}

/** HealthResponse */
export interface HealthResponse {
  /** Status */
  status: string;
}

/** ValidationError */
export interface ValidationError {
  /** Location */
  loc: (string | number)[];
  /** Message */
  msg: string;
  /** Error Type */
  type: string;
}

/** WordPressConnection */
export interface WordPressConnection {
  /**
   * Site Url
   * @format uri
   * @minLength 1
   * @maxLength 2083
   */
  site_url: string;
  /** Username */
  username: string;
  /** Application Password */
  application_password: string;
  /** Id */
  id: string;
  /**
   * Is Validated
   * @default false
   */
  is_validated?: boolean;
  /** Last Validated At */
  last_validated_at?: string | null;
}

/** WordPressConnectionCreate */
export interface WordPressConnectionCreate {
  /**
   * Site Url
   * @format uri
   * @minLength 1
   * @maxLength 2083
   */
  site_url: string;
  /** Username */
  username: string;
  /** Application Password */
  application_password: string;
}

export type CheckHealthData = HealthResponse;

/** Response List Wordpress Connections */
export type ListWordpressConnectionsData = WordPressConnection[];

export type AddWordpressConnectionData = WordPressConnection;

export type AddWordpressConnectionError = HTTPValidationError;

export interface ValidateWordpressConnectionParams {
  /** Connection Id */
  connectionId: string;
}

export type ValidateWordpressConnectionData = WordPressConnection;

export type ValidateWordpressConnectionError = HTTPValidationError;

export type GenerateBlogContentData = BlogContentResponse;

export type GenerateBlogContentError = HTTPValidationError;
