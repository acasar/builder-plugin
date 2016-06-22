<?php namespace RainLab\Builder\Classes;

use SystemException;
use ValidationException;

/**
 * Represents and manages auto-fill data for model forms.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFormAutoFillModel extends BaseModel
{
    public $columns = [];

    protected static $fillable = ['columns'];
    
    public static function createFromModel($model)
    {
        $referenceModel = new $model;
        $tableModel = new DatabaseTableModel;
        $tableModel->load($referenceModel->getTable());

        $columns = [];
        $rawColumns = $tableModel->columns;

        foreach($rawColumns as $column) {

            // Exclude the primary key
            if($column['name'] == $referenceModel->getKeyName()) {
                continue;
            }

            $columns[] = [
                'field'   => $column['name'],
                'label'   => static::getDefaultColumnLabel($column),
                'control' => static::getDefaultColumnControl($column)
            ];
        }

        $model = new static;
        $model->fill(['columns' => $columns]);

        return $model;
    }

    protected static function getDefaultColumnLabel($column)
    {
        return ucfirst(str_replace('_', ' ', snake_case($column['name'])));
    }

    protected static function getDefaultColumnControl($column)
    {
        switch($column['type']) {
            case 'smallint':
            case 'integer':
            case 'bigint':
                return ends_with($column['name'], '_id') ? 'relation' : 'number';
            case 'decimal':
            case 'float':
                return 'number';
            case 'text':
                return 'textarea';
            case 'boolean':
                return 'switch';
            case 'date':
            case 'time':
            case 'datetime':
                return 'datepicker';
            default:
                return 'text';
        }
    }
}