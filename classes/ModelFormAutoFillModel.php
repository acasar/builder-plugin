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
    
    public static function createFromModel($referenceModelClass)
    {
        $model = new static;
        $tableModel = new DatabaseTableModel;
        $referenceModel = new $referenceModelClass;
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
                'label'   => $model->getDefaultColumnLabel($column),
                'control' => $model->getDefaultColumnControl($column)
            ];
        }

        $model->fill(['columns' => $columns]);

        return $model;
    }

    protected function getDefaultColumnLabel($column)
    {
        return ucfirst(str_replace('_', ' ', snake_case($column['name'])));
    }

    protected function getDefaultColumnControl($column)
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