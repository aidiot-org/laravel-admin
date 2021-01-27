<?php

namespace Encore\Admin\Grid\Filter;

use Encore\Admin\Admin;
use Illuminate\Support\Arr;

class Between extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected $view = 'admin::filter.between';

    /**
     * Format id.
     *
     * @param string $column
     *
     * @return array|string
     */
    public function formatId($column)
    {
        $id = str_replace('.', '_', $column);

        return ['start' => "{$id}_start", 'end' => "{$id}_end"];
    }

    /**
     * Format two field names of this filter.
     *
     * @param string $column
     *
     * @return array
     */
    protected function formatName($column)
    {
        $columns = explode('.', $column);

        if (count($columns) == 1) {
            $name = $columns[0];
        } else {
            $name = array_shift($columns);

            foreach ($columns as $column) {
                $name .= "[$column]";
            }
        }

        return ['start' => "{$name}[start]", 'end' => "{$name}[end]"];
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return mixed
     */
    public function condition($inputs)
    {
        if ($this->ignore) {
            return;
        }

        if (!Arr::has($inputs, $this->column)) {
            return;
        }

        $this->value = Arr::get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (!empty($value['type'])) {
            unset($value['type']);
        }

        if (empty($value)) {
            return;
        }

        if (!empty($value['type']) && $value['type'] === 'date') {
            if (isset($value['start'])) {
                $value['start'] = $value['start'] . ' 00:00:00';
                $this->value['start'] = $this->value['start'] . ' 00:00:00';
            }
            if (isset($value['end'])) {
                $value['end'] = $value['end'] . ' 23:59:59';
                $this->value['end'] = $this->value['end'] . ' 23:59:59';
            }
        }

        if (!isset($value['start'])) {
            return $this->buildCondition($this->column, '<=', $value['end']);
        }

        if (!isset($value['end'])) {
            return $this->buildCondition($this->column, '>=', $value['start']);
        }

        $this->query = 'whereBetween';

        return $this->buildCondition($this->column, $this->value);
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function datetime($options = [])
    {
        $this->view = 'admin::filter.betweenDatetime';

        $this->setupDatetime($options);

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setupDatetime($options = [])
    {
        $options['format'] = Arr::get($options, 'format', 'YYYY-MM-DD HH:mm:ss');
        $options['locale'] = Arr::get($options, 'locale', config('app.locale'));

        $startOptions = json_encode($options);
        $endOptions = json_encode($options + ['useCurrent' => false]);

        $script = <<<EOT
            $('#{$this->id['start']}').datetimepicker($startOptions);
            $('#{$this->id['end']}').datetimepicker($endOptions);
            $("#{$this->id['start']}").on("dp.change", function (e) {
                $('#{$this->id['end']}').data("DateTimePicker").minDate(e.date);
            });
            $("#{$this->id['end']}").on("dp.change", function (e) {
                $('#{$this->id['start']}').data("DateTimePicker").maxDate(e.date);
            });
EOT;

        Admin::script($script);
    }
    
    /**
     * @param array $options
     *
     * @return $this
     */
    public function date($options = [])
    {
        $this->view = 'admin::filter.betweenDate';

        $this->setupDate($options);

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setupDate($options = [])
    {
        $options['format'] = Arr::get($options, 'format', 'YYYY-MM-DD');
        $options['locale'] = Arr::get($options, 'locale', config('app.locale'));

        $startOptions = json_encode($options);
        $endOptions = json_encode($options + ['useCurrent' => false]);

        $script = <<<EOT
            $('#{$this->id['start']}').datetimepicker($startOptions);
            $('#{$this->id['end']}').datetimepicker($endOptions);
            $("#{$this->id['start']}").on("dp.change", function (e) {
                $('#{$this->id['end']}').data("DateTimePicker").minDate(e.date);
            });
            $("#{$this->id['end']}").on("dp.change", function (e) {
                $('#{$this->id['start']}').data("DateTimePicker").maxDate(e.date);
            });
EOT;

        Admin::script($script);
    }
}
