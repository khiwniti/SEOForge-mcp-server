import {
  AddWordpressConnectionData,
  AddWordpressConnectionError,
  BlogTopicRequest,
  CheckHealthData,
  GenerateBlogContentData,
  GenerateBlogContentError,
  ListWordpressConnectionsData,
  ValidateWordpressConnectionData,
  ValidateWordpressConnectionError,
  ValidateWordpressConnectionParams,
  WordPressConnectionCreate,
} from "./data-contracts";
import { ContentType, HttpClient, RequestParams } from "./http-client";

export class Brain<SecurityDataType = unknown> extends HttpClient<SecurityDataType> {
  /**
   * @description Check health of application. Returns 200 when OK, 500 when not.
   *
   * @name check_health
   * @summary Check Health
   * @request GET:/_healthz
   */
  check_health = (params: RequestParams = {}) =>
    this.request<CheckHealthData, any>({
      path: `/_healthz`,
      method: "GET",
      ...params,
    });

  /**
   * @description Lists all stored WordPress site connections.
   *
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name list_wordpress_connections
   * @summary List Wordpress Connections
   * @request GET:/routes/wordpress-manager/connections
   */
  list_wordpress_connections = (params: RequestParams = {}) =>
    this.request<ListWordpressConnectionsData, any>({
      path: `/routes/wordpress-manager/connections`,
      method: "GET",
      ...params,
    });

  /**
   * @description Adds a new WordPress site connection.
   *
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name add_wordpress_connection
   * @summary Add Wordpress Connection
   * @request POST:/routes/wordpress-manager/connections
   */
  add_wordpress_connection = (data: WordPressConnectionCreate, params: RequestParams = {}) =>
    this.request<AddWordpressConnectionData, AddWordpressConnectionError>({
      path: `/routes/wordpress-manager/connections`,
      method: "POST",
      body: data,
      type: ContentType.Json,
      ...params,
    });

  /**
   * @description Validates a WordPress site connection by attempting to access its REST API.
   *
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name validate_wordpress_connection
   * @summary Validate Wordpress Connection
   * @request POST:/routes/wordpress-manager/connections/{connection_id}/validate
   */
  validate_wordpress_connection = (
    { connectionId, ...query }: ValidateWordpressConnectionParams,
    params: RequestParams = {},
  ) =>
    this.request<ValidateWordpressConnectionData, ValidateWordpressConnectionError>({
      path: `/routes/wordpress-manager/connections/${connectionId}/validate`,
      method: "POST",
      ...params,
    });

  /**
   * @description Generates blog content based on a given topic and optional keywords using OpenAI. This is the first version and will be enhanced with more SEO features.
   *
   * @tags Blog Generator, dbtn/module:blog_generator, dbtn/hasAuth
   * @name generate_blog_content
   * @summary Generate Blog Content
   * @request POST:/routes/blog-generator/generate
   */
  generate_blog_content = (data: BlogTopicRequest, params: RequestParams = {}) =>
    this.request<GenerateBlogContentData, GenerateBlogContentError>({
      path: `/routes/blog-generator/generate`,
      method: "POST",
      body: data,
      type: ContentType.Json,
      ...params,
    });
}
