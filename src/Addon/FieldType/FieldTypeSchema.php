<?php namespace Anomaly\Streams\Platform\Addon\FieldType;

use Anomaly\Streams\Platform\Assignment\Contract\AssignmentInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * Class FieldTypeSchema
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Addon\FieldType
 */
class FieldTypeSchema
{

    /**
     * The schema builder object.
     *
     * @var Builder
     */
    protected $schema;

    /**
     * The field type object.
     *
     * @var FieldType
     */
    protected $fieldType;

    /**
     * Create a new FieldTypeSchema instance.
     *
     * @param FieldType $fieldType
     */
    public function __construct(FieldType $fieldType)
    {
        $this->fieldType = $fieldType;

        $this->schema = app('db')->connection()->getSchemaBuilder();
    }

    /**
     * Add the field type column to the table.
     *
     * @param Blueprint           $table
     * @param AssignmentInterface $assignment
     */
    public function addColumn(Blueprint $table, AssignmentInterface $assignment)
    {
        // Skip if no column type.
        if (!$this->fieldType->getColumnType()) {
            return;
        }

        // Skip if the column already exists.
        if ($this->schema->hasColumn($table->getTable(), $this->fieldType->getColumnName())) {
            return;
        }

        // Add the column.
        if (!$assignment->isTranslatable()) {

            /**
             * If the assignment is NOT translatable then it
             * can be required and also have a default value.
             */
            $column = $table->{$this->fieldType->getColumnType()}($this->fieldType->getColumnName())
                ->nullable(!$assignment->isRequired());

            try {
                $column->default(array_get($this->fieldType->getConfig(), 'default_value'));
            } catch (\Exception $e) {
                // Doesn't support default values.
            }
        } else {

            /**
             * If the assignment is translatable then it
             * must be nullable cause translations are not
             * required input.
             */
            $table->{$this->fieldType->getColumnType()}($this->fieldType->getColumnName())
                ->nullable(true);
        }

        // Mark the column unique if desired and not translatable.
        if ($assignment->isUnique() && !$assignment->isTranslatable()) {
            $table->unique($this->fieldType->getColumnName());
        }
    }

    /**
     * Change the field type column.
     *
     * @param Blueprint           $table
     * @param AssignmentInterface $assignment
     */
    public function changeColumn(Blueprint $table, AssignmentInterface $assignment)
    {
        // Skip if no column type.
        if (!$this->fieldType->getColumnType()) {
            return;
        }
    }

    /**
     * Drop the field type column from the table.
     *
     * @param Blueprint $table
     */
    public function dropColumn(Blueprint $table)
    {
        // Skip if no column type.
        if (!$this->fieldType->getColumnType()) {
            return;
        }

        // Skip if the column doesn't exist.
        if (!$this->schema->hasColumn($table->getTable(), $this->fieldType->getColumnName())) {
            return;
        }

        // Drop dat 'ole column.
        $table->dropColumn($this->fieldType->getColumnName());
    }
}
