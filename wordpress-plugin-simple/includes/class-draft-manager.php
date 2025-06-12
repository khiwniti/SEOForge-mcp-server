<?php
/**
 * Draft Manager for Universal MCP Plugin
 * Handles saving and managing content drafts in custom table
 */

if (!defined('ABSPATH')) {
    exit;
}

class UMCP_Draft_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'umcp_drafts';
        
        add_action('admin_menu', array($this, 'add_drafts_page'));
        add_action('wp_ajax_umcp_save_draft', array($this, 'ajax_save_draft'));
        add_action('wp_ajax_umcp_load_draft', array($this, 'ajax_load_draft'));
        add_action('wp_ajax_umcp_delete_draft', array($this, 'ajax_delete_draft'));
        add_action('wp_ajax_umcp_publish_draft', array($this, 'ajax_publish_draft'));
        add_action('wp_ajax_umcp_duplicate_draft', array($this, 'ajax_duplicate_draft'));
        
        // Schedule cleanup
        if (!wp_next_scheduled('umcp_cleanup_drafts')) {
            wp_schedule_event(time(), 'daily', 'umcp_cleanup_drafts');
        }
        add_action('umcp_cleanup_drafts', array($this, 'cleanup_old_drafts'));
    }
    
    /**
     * Create drafts table on plugin activation
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'umcp_drafts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            keywords text,
            language varchar(10) DEFAULT 'en',
            tone varchar(50) DEFAULT 'professional',
            length varchar(20) DEFAULT 'medium',
            industry varchar(100) DEFAULT 'general',
            images longtext,
            seo_data longtext,
            word_count int(11) DEFAULT 0,
            generation_mode varchar(20) DEFAULT 'keywords',
            ai_model varchar(50) DEFAULT 'gemini-1.5-flash',
            user_id bigint(20) NOT NULL,
            status varchar(20) DEFAULT 'draft',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            published_post_id bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add drafts management page
     */
    public function add_drafts_page() {
        add_submenu_page(
            'edit.php',
            'AI Content Drafts',
            'AI Drafts',
            'edit_posts',
            'umcp-drafts',
            array($this, 'drafts_page_content')
        );
    }
    
    /**
     * Save draft to database
     */
    public function save_draft($data) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            return false;
        }
        
        // Extract title from content if not provided
        $title = $data['title'] ?? $this->extract_title_from_content($data['content'] ?? '');
        
        $draft_data = array(
            'title' => sanitize_text_field($title),
            'content' => wp_kses_post($data['content'] ?? ''),
            'keywords' => sanitize_text_field(is_array($data['keywords']) ? implode(', ', $data['keywords']) : $data['keywords']),
            'language' => sanitize_text_field($data['language'] ?? 'en'),
            'tone' => sanitize_text_field($data['tone'] ?? 'professional'),
            'length' => sanitize_text_field($data['length'] ?? 'medium'),
            'industry' => sanitize_text_field($data['industry'] ?? 'general'),
            'images' => maybe_serialize($data['images'] ?? array()),
            'seo_data' => maybe_serialize($data['seo_data'] ?? array()),
            'word_count' => $this->count_words($data['content'] ?? ''),
            'generation_mode' => sanitize_text_field($data['generation_mode'] ?? 'keywords'),
            'ai_model' => sanitize_text_field($data['ai_model'] ?? 'gemini-1.5-flash'),
            'user_id' => $user_id,
            'status' => 'draft'
        );
        
        $result = $wpdb->insert($this->table_name, $draft_data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get drafts for current user
     */
    public function get_user_drafts($user_id = null, $limit = 20, $offset = 0) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE user_id = %d 
             ORDER BY updated_at DESC 
             LIMIT %d OFFSET %d",
            $user_id, $limit, $offset
        );
        
        $drafts = $wpdb->get_results($sql);
        
        // Unserialize data
        foreach ($drafts as $draft) {
            $draft->images = maybe_unserialize($draft->images);
            $draft->seo_data = maybe_unserialize($draft->seo_data);
        }
        
        return $drafts;
    }
    
    /**
     * Get single draft
     */
    public function get_draft($id) {
        global $wpdb;
        
        $draft = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
        
        if ($draft) {
            $draft->images = maybe_unserialize($draft->images);
            $draft->seo_data = maybe_unserialize($draft->seo_data);
        }
        
        return $draft;
    }
    
    /**
     * Delete draft
     */
    public function delete_draft($id) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id, 'user_id' => $user_id),
            array('%d', '%d')
        );
    }
    
    /**
     * Publish draft as WordPress post
     */
    public function publish_draft($id, $post_data = array()) {
        $draft = $this->get_draft($id);
        
        if (!$draft || $draft->user_id != get_current_user_id()) {
            return false;
        }
        
        // Prepare post data
        $default_post_data = array(
            'post_title' => $draft->title,
            'post_content' => $draft->content,
            'post_status' => get_option('umcp_default_post_status', 'draft'),
            'post_author' => get_option('umcp_default_post_author', $draft->user_id),
            'post_category' => array(get_option('umcp_default_post_category', 1)),
            'post_type' => 'post'
        );
        
        $post_data = array_merge($default_post_data, $post_data);
        
        // Create post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Add meta data
        update_post_meta($post_id, '_umcp_keywords', $draft->keywords);
        update_post_meta($post_id, '_umcp_language', $draft->language);
        update_post_meta($post_id, '_umcp_tone', $draft->tone);
        update_post_meta($post_id, '_umcp_ai_model', $draft->ai_model);
        update_post_meta($post_id, '_umcp_generation_mode', $draft->generation_mode);
        update_post_meta($post_id, '_umcp_draft_id', $draft->id);
        
        // Update draft with published post ID
        global $wpdb;
        $wpdb->update(
            $this->table_name,
            array('published_post_id' => $post_id, 'status' => 'published'),
            array('id' => $id),
            array('%d', '%s'),
            array('%d')
        );
        
        return $post_id;
    }
    
    /**
     * Publish draft data directly as WordPress post (without saving as draft first)
     */
    public function publish_draft_data($draft_data, $post_data = array()) {
        // Prepare post data
        $default_post_data = array(
            'post_title' => $draft_data['title'] ?? 'Untitled',
            'post_content' => $draft_data['content'] ?? '',
            'post_status' => get_option('umcp_default_post_status', 'draft'),
            'post_author' => get_option('umcp_default_post_author', get_current_user_id()),
            'post_category' => array(get_option('umcp_default_post_category', 1)),
            'post_type' => 'post'
        );
        
        $post_data = array_merge($default_post_data, $post_data);
        
        // Create post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return false;
        }
        
        // Add meta data
        $keywords = is_array($draft_data['keywords']) ? implode(', ', $draft_data['keywords']) : $draft_data['keywords'];
        update_post_meta($post_id, '_umcp_keywords', $keywords);
        update_post_meta($post_id, '_umcp_language', $draft_data['language'] ?? 'en');
        update_post_meta($post_id, '_umcp_tone', $draft_data['tone'] ?? 'professional');
        update_post_meta($post_id, '_umcp_ai_model', $draft_data['ai_model'] ?? 'gemini-1.5-flash');
        update_post_meta($post_id, '_umcp_generation_mode', $draft_data['generation_mode'] ?? 'keywords');
        
        return $post_id;
    }
    
    /**
     * Drafts page content
     */
    public function drafts_page_content() {
        $action = $_GET['action'] ?? 'list';
        $draft_id = $_GET['draft_id'] ?? 0;
        
        switch ($action) {
            case 'view':
                $this->view_draft_page($draft_id);
                break;
            case 'edit':
                $this->edit_draft_page($draft_id);
                break;
            default:
                $this->list_drafts_page();
                break;
        }
    }
    
    /**
     * List drafts page
     */
    private function list_drafts_page() {
        $drafts = $this->get_user_drafts();
        $total_drafts = $this->count_user_drafts();
        ?>
        <div class="wrap">
            <h1>📝 AI Content Drafts</h1>
            
            <div class="umcp-drafts-header">
                <div class="umcp-stats">
                    <div class="umcp-stat-item">
                        <span class="umcp-stat-number"><?php echo $total_drafts; ?></span>
                        <span class="umcp-stat-label">Total Drafts</span>
                    </div>
                    <div class="umcp-stat-item">
                        <span class="umcp-stat-number"><?php echo $this->count_published_drafts(); ?></span>
                        <span class="umcp-stat-label">Published</span>
                    </div>
                    <div class="umcp-stat-item">
                        <span class="umcp-stat-number"><?php echo $this->count_words_total(); ?></span>
                        <span class="umcp-stat-label">Total Words</span>
                    </div>
                </div>
                
                <div class="umcp-actions">
                    <a href="<?php echo admin_url('admin.php?page=universal-mcp'); ?>" class="button button-primary">
                        ➕ Create New Content
                    </a>
                    <button type="button" id="bulk-delete" class="button" disabled>
                        🗑️ Delete Selected
                    </button>
                    <button type="button" id="bulk-publish" class="button" disabled>
                        📤 Publish Selected
                    </button>
                </div>
            </div>
            
            <?php if (empty($drafts)): ?>
                <div class="umcp-empty-state">
                    <div class="umcp-empty-icon">📝</div>
                    <h2>No drafts yet</h2>
                    <p>Start creating AI-powered content to see your drafts here.</p>
                    <a href="<?php echo admin_url('admin.php?page=universal-mcp'); ?>" class="button button-primary">
                        🚀 Create Your First Content
                    </a>
                </div>
            <?php else: ?>
                <form id="drafts-form">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column">
                                    <input type="checkbox" id="cb-select-all">
                                </td>
                                <th class="manage-column column-title">Title</th>
                                <th class="manage-column column-keywords">Keywords</th>
                                <th class="manage-column column-stats">Stats</th>
                                <th class="manage-column column-meta">Details</th>
                                <th class="manage-column column-date">Date</th>
                                <th class="manage-column column-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drafts as $draft): ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="draft_ids[]" value="<?php echo $draft->id; ?>">
                                    </th>
                                    <td class="column-title">
                                        <strong>
                                            <a href="<?php echo admin_url('edit.php?page=umcp-drafts&action=view&draft_id=' . $draft->id); ?>">
                                                <?php echo esc_html($draft->title); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span class="view">
                                                <a href="<?php echo admin_url('edit.php?page=umcp-drafts&action=view&draft_id=' . $draft->id); ?>">👁️ View</a> |
                                            </span>
                                            <span class="edit">
                                                <a href="<?php echo admin_url('edit.php?page=umcp-drafts&action=edit&draft_id=' . $draft->id); ?>">✏️ Edit</a> |
                                            </span>
                                            <span class="publish">
                                                <a href="#" class="publish-draft" data-id="<?php echo $draft->id; ?>">📤 Publish</a> |
                                            </span>
                                            <span class="duplicate">
                                                <a href="#" class="duplicate-draft" data-id="<?php echo $draft->id; ?>">📋 Duplicate</a> |
                                            </span>
                                            <span class="delete">
                                                <a href="#" class="delete-draft" data-id="<?php echo $draft->id; ?>" style="color: #a00;">🗑️ Delete</a>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="column-keywords">
                                        <span class="umcp-keywords"><?php echo esc_html($draft->keywords); ?></span>
                                    </td>
                                    <td class="column-stats">
                                        <div class="umcp-stats-mini">
                                            <span class="umcp-word-count">📊 <?php echo number_format($draft->word_count); ?> words</span>
                                            <span class="umcp-reading-time">⏱️ <?php echo max(1, ceil($draft->word_count / 200)); ?> min read</span>
                                            <?php if (!empty($draft->images)): ?>
                                                <span class="umcp-image-count">🖼️ <?php echo count($draft->images); ?> images</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="column-meta">
                                        <div class="umcp-meta">
                                            <span class="umcp-language">🌐 <?php echo strtoupper($draft->language); ?></span>
                                            <span class="umcp-tone">🎭 <?php echo ucfirst($draft->tone); ?></span>
                                            <span class="umcp-mode">🤖 <?php echo ucfirst($draft->generation_mode); ?></span>
                                            <?php if ($draft->published_post_id): ?>
                                                <span class="umcp-published">
                                                    ✅ <a href="<?php echo get_edit_post_link($draft->published_post_id); ?>">Published</a>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="column-date">
                                        <div class="umcp-date">
                                            <strong><?php echo date('M j, Y', strtotime($draft->created_at)); ?></strong><br>
                                            <small><?php echo date('g:i a', strtotime($draft->created_at)); ?></small>
                                            <?php if ($draft->updated_at !== $draft->created_at): ?>
                                                <br><small class="umcp-updated">Updated: <?php echo date('M j', strtotime($draft->updated_at)); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="column-actions">
                                        <div class="umcp-quick-actions">
                                            <button type="button" class="button button-small publish-draft" data-id="<?php echo $draft->id; ?>">
                                                📤 Publish
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            <?php endif; ?>
        </div>
        
        <style>
        .umcp-drafts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .umcp-stats {
            display: flex;
            gap: 30px;
        }
        
        .umcp-stat-item {
            text-align: center;
        }
        
        .umcp-stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .umcp-stat-label {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .umcp-actions {
            display: flex;
            gap: 10px;
        }
        
        .umcp-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .umcp-empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .umcp-keywords {
            display: inline-block;
            background: #f0f8ff;
            color: #0073aa;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .umcp-stats-mini {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .umcp-stats-mini span {
            font-size: 11px;
            color: #666;
        }
        
        .umcp-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .umcp-meta span {
            font-size: 11px;
            color: #666;
        }
        
        .umcp-published a {
            color: #46b450;
            text-decoration: none;
        }
        
        .umcp-date {
            font-size: 12px;
        }
        
        .umcp-updated {
            color: #666;
            font-style: italic;
        }
        
        .umcp-quick-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Bulk selection
            $('#cb-select-all').change(function() {
                $('input[name="draft_ids[]"]').prop('checked', this.checked);
                toggleBulkActions();
            });
            
            $('input[name="draft_ids[]"]').change(function() {
                toggleBulkActions();
            });
            
            function toggleBulkActions() {
                var checked = $('input[name="draft_ids[]"]:checked').length;
                $('#bulk-delete, #bulk-publish').prop('disabled', checked === 0);
            }
            
            // Individual actions
            $('.publish-draft').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                
                if (confirm('Publish this draft as a WordPress post?')) {
                    publishDraft(id);
                }
            });
            
            $('.duplicate-draft').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                duplicateDraft(id);
            });
            
            $('.delete-draft').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                
                if (confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
                    deleteDraft(id);
                }
            });
            
            // Bulk actions
            $('#bulk-publish').click(function() {
                var ids = $('input[name="draft_ids[]"]:checked').map(function() {
                    return this.value;
                }).get();
                
                if (ids.length && confirm('Publish ' + ids.length + ' selected drafts?')) {
                    bulkPublish(ids);
                }
            });
            
            $('#bulk-delete').click(function() {
                var ids = $('input[name="draft_ids[]"]:checked').map(function() {
                    return this.value;
                }).get();
                
                if (ids.length && confirm('Delete ' + ids.length + ' selected drafts? This action cannot be undone.')) {
                    bulkDelete(ids);
                }
            });
            
            function publishDraft(id) {
                $.post(ajaxurl, {
                    action: 'umcp_publish_draft',
                    draft_id: id,
                    nonce: '<?php echo wp_create_nonce('umcp_draft_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ Draft published successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                });
            }
            
            function duplicateDraft(id) {
                $.post(ajaxurl, {
                    action: 'umcp_duplicate_draft',
                    draft_id: id,
                    nonce: '<?php echo wp_create_nonce('umcp_draft_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('✅ Draft duplicated successfully!');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                });
            }
            
            function deleteDraft(id) {
                $.post(ajaxurl, {
                    action: 'umcp_delete_draft',
                    draft_id: id,
                    nonce: '<?php echo wp_create_nonce('umcp_draft_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('input[value="' + id + '"]').closest('tr').fadeOut();
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                });
            }
            
            function bulkPublish(ids) {
                var completed = 0;
                var total = ids.length;
                
                ids.forEach(function(id) {
                    publishDraft(id);
                });
            }
            
            function bulkDelete(ids) {
                var completed = 0;
                var total = ids.length;
                
                ids.forEach(function(id) {
                    deleteDraft(id);
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * View single draft page
     */
    private function view_draft_page($draft_id) {
        $draft = $this->get_draft($draft_id);
        
        if (!$draft || $draft->user_id != get_current_user_id()) {
            wp_die('Draft not found or access denied.');
        }
        
        ?>
        <div class="wrap">
            <h1>📖 View Draft: <?php echo esc_html($draft->title); ?></h1>
            
            <div class="umcp-draft-header">
                <div class="umcp-draft-meta">
                    <span class="umcp-meta-item">🗓️ Created: <?php echo date('F j, Y g:i a', strtotime($draft->created_at)); ?></span>
                    <span class="umcp-meta-item">📊 <?php echo number_format($draft->word_count); ?> words</span>
                    <span class="umcp-meta-item">⏱️ <?php echo max(1, ceil($draft->word_count / 200)); ?> min read</span>
                    <span class="umcp-meta-item">🌐 <?php echo strtoupper($draft->language); ?></span>
                    <span class="umcp-meta-item">🎭 <?php echo ucfirst($draft->tone); ?></span>
                </div>
                
                <div class="umcp-draft-actions">
                    <a href="<?php echo admin_url('edit.php?page=umcp-drafts&action=edit&draft_id=' . $draft->id); ?>" class="button">
                        ✏️ Edit
                    </a>
                    <button type="button" class="button button-primary publish-draft" data-id="<?php echo $draft->id; ?>">
                        📤 Publish as Post
                    </button>
                    <a href="<?php echo admin_url('edit.php?page=umcp-drafts'); ?>" class="button">
                        ← Back to Drafts
                    </a>
                </div>
            </div>
            
            <div class="umcp-draft-content">
                <div class="umcp-content-section">
                    <h2>📝 Content</h2>
                    <div class="umcp-content-preview">
                        <?php echo wpautop($draft->content); ?>
                    </div>
                </div>
                
                <?php if (!empty($draft->keywords)): ?>
                    <div class="umcp-keywords-section">
                        <h3>🏷️ Keywords</h3>
                        <div class="umcp-keywords-list">
                            <?php
                            $keywords = explode(',', $draft->keywords);
                            foreach ($keywords as $keyword) {
                                echo '<span class="umcp-keyword-tag">' . esc_html(trim($keyword)) . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($draft->images)): ?>
                    <div class="umcp-images-section">
                        <h3>🖼️ Generated Images</h3>
                        <div class="umcp-images-grid">
                            <?php foreach ($draft->images as $image): ?>
                                <div class="umcp-image-item">
                                    <img src="<?php echo esc_url($image['url']); ?>" alt="Generated image" />
                                    <p class="umcp-image-prompt"><?php echo esc_html($image['prompt']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .umcp-draft-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .umcp-draft-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .umcp-meta-item {
            font-size: 14px;
            color: #666;
        }
        
        .umcp-draft-actions {
            display: flex;
            gap: 10px;
        }
        
        .umcp-draft-content {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 30px;
        }
        
        .umcp-content-section {
            margin-bottom: 30px;
        }
        
        .umcp-content-preview {
            line-height: 1.6;
            font-size: 16px;
        }
        
        .umcp-keywords-section {
            margin: 30px 0;
        }
        
        .umcp-keywords-list {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .umcp-keyword-tag {
            background: #0073aa;
            color: white;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
        }
        
        .umcp-images-section {
            margin: 30px 0;
        }
        
        .umcp-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .umcp-image-item img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .umcp-image-prompt {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            font-style: italic;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.publish-draft').click(function() {
                var id = $(this).data('id');
                
                if (confirm('Publish this draft as a WordPress post?')) {
                    $.post(ajaxurl, {
                        action: 'umcp_publish_draft',
                        draft_id: id,
                        nonce: '<?php echo wp_create_nonce('umcp_draft_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('✅ Draft published successfully!');
                            window.location.href = '<?php echo admin_url('edit.php?page=umcp-drafts'); ?>';
                        } else {
                            alert('❌ Error: ' + response.data);
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handlers
     */
    public function ajax_save_draft() {
        check_ajax_referer('umcp_draft_nonce', 'nonce');
        
        $data = $_POST['draft_data'] ?? array();
        $draft_id = $this->save_draft($data);
        
        if ($draft_id) {
            wp_send_json_success(array('draft_id' => $draft_id));
        } else {
            wp_send_json_error('Failed to save draft');
        }
    }
    
    public function ajax_delete_draft() {
        check_ajax_referer('umcp_draft_nonce', 'nonce');
        
        $draft_id = intval($_POST['draft_id']);
        $result = $this->delete_draft($draft_id);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete draft');
        }
    }
    
    public function ajax_publish_draft() {
        check_ajax_referer('umcp_draft_nonce', 'nonce');
        
        $draft_id = intval($_POST['draft_id']);
        $post_id = $this->publish_draft($draft_id);
        
        if ($post_id) {
            wp_send_json_success(array('post_id' => $post_id));
        } else {
            wp_send_json_error('Failed to publish draft');
        }
    }
    
    public function ajax_duplicate_draft() {
        check_ajax_referer('umcp_draft_nonce', 'nonce');
        
        $draft_id = intval($_POST['draft_id']);
        $draft = $this->get_draft($draft_id);
        
        if (!$draft) {
            wp_send_json_error('Draft not found');
            return;
        }
        
        // Create duplicate
        $duplicate_data = array(
            'title' => $draft->title . ' (Copy)',
            'content' => $draft->content,
            'keywords' => $draft->keywords,
            'language' => $draft->language,
            'tone' => $draft->tone,
            'length' => $draft->length,
            'industry' => $draft->industry,
            'images' => $draft->images,
            'seo_data' => $draft->seo_data,
            'generation_mode' => $draft->generation_mode,
            'ai_model' => $draft->ai_model
        );
        
        $new_draft_id = $this->save_draft($duplicate_data);
        
        if ($new_draft_id) {
            wp_send_json_success(array('draft_id' => $new_draft_id));
        } else {
            wp_send_json_error('Failed to duplicate draft');
        }
    }
    
    /**
     * Helper methods
     */
    private function extract_title_from_content($content) {
        // Try to extract title from markdown heading
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        
        // Try to extract from HTML heading
        if (preg_match('/<h[1-6][^>]*>(.+?)<\/h[1-6]>/i', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }
        
        // Use first sentence
        $sentences = preg_split('/[.!?]+/', strip_tags($content));
        if (!empty($sentences[0])) {
            return trim(substr($sentences[0], 0, 100));
        }
        
        return 'Untitled Draft';
    }
    
    private function count_words($content) {
        return str_word_count(strip_tags($content));
    }
    
    private function count_user_drafts($user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d",
            $user_id
        ));
    }
    
    private function count_published_drafts($user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND published_post_id IS NOT NULL",
            $user_id
        ));
    }
    
    private function count_words_total($user_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(word_count) FROM {$this->table_name} WHERE user_id = %d",
            $user_id
        ));
        
        return $result ? number_format($result) : '0';
    }
    
    /**
     * Cleanup old drafts
     */
    public function cleanup_old_drafts() {
        if (!get_option('umcp_auto_cleanup_drafts', '1')) {
            return;
        }
        
        global $wpdb;
        
        $retention_days = get_option('umcp_draft_retention_days', 30);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} 
             WHERE created_at < %s 
             AND status = 'draft' 
             AND published_post_id IS NULL",
            $cutoff_date
        ));
    }
}