import {
  AddWordpressConnectionData,
  BlogTopicRequest,
  CheckHealthData,
  GenerateBlogContentData,
  ListWordpressConnectionsData,
  ValidateWordpressConnectionData,
  WordPressConnectionCreate,
} from "./data-contracts";

export namespace Brain {
  /**
   * @description Check health of application. Returns 200 when OK, 500 when not.
   * @name check_health
   * @summary Check Health
   * @request GET:/_healthz
   */
  export namespace check_health {
    export type RequestParams = {};
    export type RequestQuery = {};
    export type RequestBody = never;
    export type RequestHeaders = {};
    export type ResponseBody = CheckHealthData;
  }

  /**
   * @description Lists all stored WordPress site connections.
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name list_wordpress_connections
   * @summary List Wordpress Connections
   * @request GET:/routes/wordpress-manager/connections
   */
  export namespace list_wordpress_connections {
    export type RequestParams = {};
    export type RequestQuery = {};
    export type RequestBody = never;
    export type RequestHeaders = {};
    export type ResponseBody = ListWordpressConnectionsData;
  }

  /**
   * @description Adds a new WordPress site connection.
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name add_wordpress_connection
   * @summary Add Wordpress Connection
   * @request POST:/routes/wordpress-manager/connections
   */
  export namespace add_wordpress_connection {
    export type RequestParams = {};
    export type RequestQuery = {};
    export type RequestBody = WordPressConnectionCreate;
    export type RequestHeaders = {};
    export type ResponseBody = AddWordpressConnectionData;
  }

  /**
   * @description Validates a WordPress site connection by attempting to access its REST API.
   * @tags WordPress Management, dbtn/module:wordpress_manager, dbtn/hasAuth
   * @name validate_wordpress_connection
   * @summary Validate Wordpress Connection
   * @request POST:/routes/wordpress-manager/connections/{connection_id}/validate
   */
  export namespace validate_wordpress_connection {
    export type RequestParams = {
      /** Connection Id */
      connectionId: string;
    };
    export type RequestQuery = {};
    export type RequestBody = never;
    export type RequestHeaders = {};
    export type ResponseBody = ValidateWordpressConnectionData;
  }

  /**
   * @description Generates blog content based on a given topic and optional keywords using OpenAI. This is the first version and will be enhanced with more SEO features.
   * @tags Blog Generator, dbtn/module:blog_generator, dbtn/hasAuth
   * @name generate_blog_content
   * @summary Generate Blog Content
   * @request POST:/routes/blog-generator/generate
   */
  export namespace generate_blog_content {
    export type RequestParams = {};
    export type RequestQuery = {};
    export type RequestBody = BlogTopicRequest;
    export type RequestHeaders = {};
    export type ResponseBody = GenerateBlogContentData;
  }
}
