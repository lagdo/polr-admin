<?php

namespace Lagdo\Polr\Admin\Ext\Datatables;

use JsonSerializable;

class Datatables implements JsonSerializable
{
    /**
     * The datatable data
     * @var array
     */
    protected $data;

    /**
     * The datatable draw option
     * @var integer
     */
    protected $draw;

    /**
     * The number of records in the datatable before filtering
     * @var integer
     */
    protected $recordsTotal;

    /**
     * The number of records in the datatable after filtering
     * @var integer
     */
    protected $recordsFiltered;

    /**
     * The datatable row attributes
     * @var array
     */
    protected $attrs;

    /**
     * The datatable columns
     * @var array
     */
    protected $columns;

    /**
     * The columns to add to the datatable
     * @var array
     */
    protected $add;

    /**
     * The columns to edit in the datatable
     * @var array
     */
    protected $edit;

    /**
     * The columns to hide in the datatable
     * @var array
     */
    protected $hide;

    /**
     * The datatable constructor
     * @param array $data
     * @param integer $total
     * @param integer $draw
     */
    public function __construct(array $data, $total, $draw = 0)
    {
        $this->data = $data;
        $this->draw = $draw;
        $this->recordsTotal = $total;
        $this->recordsFiltered = count($data);
        $this->columns = array();
        $this->add = $this->edit = $this->hide = $this->attrs = array();
    }

    /**
     * Set the datatable row attributes
     * @param array $attrs
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function attr(array $attrs)
    {
        $this->attrs = $attrs;

        return $this;
    }

    /**
     * Set the number of records in the datatable before filtering
     * @param integer $total
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function setTotal($total)
    {
        $this->recordsTotal = $total;

        return $this;
    }

    /**
     * Set the datatable columns
     * @param array $columns
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Add a column to the datatable
     * @param string $newColumn
     * @param Closure $closure
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function add($newColumn, $closure)
    {
        $this->add[$newColumn] = $closure;

        return $this;
    }

    /**
     * Edit a column in the datatable
     * @param string $column
     * @param Closure $closure
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function edit($column, $closure)
    {
        $this->edit[$column] = $closure;

        return $this;
    }

    /**
     * Escape columns in the datatable
     * @param array $columns
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function escape(array $columns)
    {
        foreach($columns as $column)
        {
            $this->edit($column, function($data) use($column) {
                return addslashes($data->$column);
            });
        }

        return $this;
    }

    /**
     * Hide columns in the datatable
     * @param array $columns
     * @return \Lagdo\Polr\Admin\Ext\Datatables\Datatables
     */
    public function hide($columns)
    {
        if(!is_array($columns))
        {
            $columns = func_get_args();
        }
        $columns = array_intersect($this->columns, $columns);
        $this->hide = array_merge($this->hide, array_combine($columns, $columns));

        return $this;
    }

    /**
     * Convert the datatable to JSON
     * @return StdClass
     */
    public function jsonSerialize()
    {
        $tableData = [];

        foreach($this->data as $row)
        {
            // New columns..
            if(count($this->add) > 0)
            {
                foreach($this->add as $column => $closure)
                {
                    $row->$column = $closure($row);
                }
            }

            // Editing columns..
            if(count($this->edit) > 0)
            {
                foreach($this->edit as $column => $closure)
                {
                    if(isset($row->$column))
                    {
                        $row->$column = $closure($row);
                    }
                }
            }

            // Hide unwanted columns from output
            // $row = array_diff_key($row, $this->hide);

            // Row attributes..
            if(count($this->attrs) > 0)
            {
                $attrs = [];
                foreach($this->attrs as $name => $column)
                {
                    $attrs[$name] = (string)$row->$column;
                }
                $row->DT_RowAttr = (object)$attrs;
            }

            $tableData[] = $row;
        }

        return (object)[
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $tableData
        ];
    }
}
