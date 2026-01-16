<?php
/**
 * Base Model Class
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base Model Class
 * Provides common CRUD operations for all models
 */
abstract class CDC_Base_Model {
    /**
     * Database table name (without prefix)
     */
    protected $table_name;

    /**
     * Primary key column name
     */
    protected $primary_key = 'id';

    /**
     * Fillable columns
     */
    protected $fillable = array();

    /**
     * Date columns
     */
    protected $dates = array('created_at', 'updated_at');

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . $this->table_name;
    }

    /**
     * Get table name
     */
    public function get_table_name() {
        return $this->table_name;
    }

    /**
     * Find record by ID
     *
     * @param int $id Record ID
     * @return object|null
     */
    public function find($id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE {$this->primary_key} = %d",
            $id
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get all records
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_all($args = array()) {
        global $wpdb;

        $defaults = array(
            'orderby' => $this->primary_key,
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $query = "SELECT * FROM {$this->table_name}";

        if (!empty($args['where'])) {
            $query .= " WHERE " . $args['where'];
        }

        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        $query .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";

        return $wpdb->get_results($query);
    }

    /**
     * Create new record
     *
     * @param array $data Record data
     * @return int|false Insert ID or false on failure
     */
    public function create($data) {
        global $wpdb;

        // Filter only fillable columns
        $data = $this->filter_fillable($data);

        // Add timestamps
        if (in_array('created_at', $this->dates) && !isset($data['created_at'])) {
            $data['created_at'] = current_time('mysql');
        }
        if (in_array('updated_at', $this->dates) && !isset($data['updated_at'])) {
            $data['updated_at'] = current_time('mysql');
        }

        $result = $wpdb->insert($this->table_name, $data);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update record
     *
     * @param int $id Record ID
     * @param array $data Update data
     * @return bool
     */
    public function update($id, $data) {
        global $wpdb;

        // Filter only fillable columns
        $data = $this->filter_fillable($data);

        // Update timestamp
        if (in_array('updated_at', $this->dates) && !isset($data['updated_at'])) {
            $data['updated_at'] = current_time('mysql');
        }

        $result = $wpdb->update(
            $this->table_name,
            $data,
            array($this->primary_key => $id)
        );

        return $result !== false;
    }

    /**
     * Delete record
     *
     * @param int $id Record ID
     * @return bool
     */
    public function delete($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array($this->primary_key => $id)
        );

        return $result !== false;
    }

    /**
     * Count records
     *
     * @param string $where WHERE clause
     * @return int
     */
    public function count($where = '') {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$this->table_name}";

        if (!empty($where)) {
            $query .= " WHERE " . $where;
        }

        return (int) $wpdb->get_var($query);
    }

    /**
     * Search records
     *
     * @param string $search Search term
     * @param array $columns Columns to search
     * @param array $args Additional arguments
     * @return array
     */
    public function search($search, $columns = array(), $args = array()) {
        global $wpdb;

        if (empty($search) || empty($columns)) {
            return array();
        }

        $defaults = array(
            'orderby' => $this->primary_key,
            'order' => 'DESC',
            'limit' => 50,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);

        // Build WHERE clause
        $where_parts = array();
        foreach ($columns as $column) {
            $where_parts[] = $wpdb->prepare("$column LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        $where = implode(' OR ', $where_parts);

        $query = "SELECT * FROM {$this->table_name}";
        $query .= " WHERE " . $where;
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        $query .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";

        return $wpdb->get_results($query);
    }

    /**
     * Filter data to only fillable columns
     *
     * @param array $data Data to filter
     * @return array
     */
    protected function filter_fillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Execute custom query
     *
     * @param string $query SQL query
     * @return mixed
     */
    protected function query($query) {
        global $wpdb;
        return $wpdb->get_results($query);
    }

    /**
     * Get wpdb instance
     *
     * @return wpdb
     */
    protected function get_wpdb() {
        global $wpdb;
        return $wpdb;
    }
}
