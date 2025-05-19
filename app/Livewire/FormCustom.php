<?php

namespace App\Livewire;

use App\Models\FieldForm;
use App\Models\TypeOfForm;
use Illuminate\Support\Str;
use Livewire\Component;

class FormCustom extends Component
{
    public $typeOfForm;            // danh sách loại đơn
    public $type_of_form;          // ID loại đơn đang chọn
    public $selectedForm;          // Loại đơn đang được chọn (bao gồm fieldForm)
    public $customFields = [];     // Các trường người dùng đang thêm/chỉnh sửa

    public $action = 'CREATE';     // CREATE hoặc UPDATE
    public $fieldID = null;        // ID của field đang sửa

    public $selectedDataType;

    public function mount()
    {
        $this->typeOfForm = TypeOfForm::all();
    }

    public function render()
    {
        $this->selectedForm = TypeOfForm::with(['fieldForm' => function ($q) {
            $q->orderBy('order');
        }])->find($this->type_of_form);
        return view('livewire.form-custom');
    }

    public function ChoosetypeForm($formId)
    {
        $this->type_of_form = $formId;

        $this->resetFieldState();
    }

    public function addCustomField($action = 'CREATE')
    {


        $this->action = $action;
        $this->customFields[] = [
            'uuid' => (string) Str::uuid(),
            'key' => 'text',
            'label' => '',
            'options' => []
        ];
    }

    public function editCustomField($action, $id)
    {
        $field = FieldForm::findOrFail($id);

        $this->resetFieldState();

        $this->action = $action;
        $this->fieldID = $id;
        $this->customFields[] = [
            'uuid' => (string) Str::uuid(),
            'key' => $field->data_type,
            'label' => $field->label,
            'options' => in_array($field->data_type, ['checkbox', 'radio']) ? ($field->options ?? []) : [],
        ];
    }

    public function removeCustomField($index)
    {
        unset($this->customFields[$index]);
        $this->customFields = array_values($this->customFields); // reindex
    }

    public function save()
    {
        $this->validate([
            'type_of_form' => 'required',
            'customFields' => 'array|min:1',
            'customFields.*.label' => 'required|string',
        ]);

        foreach ($this->customFields as $field) {
            if (in_array($field['key'], ['checkbox', 'radio']) && empty(array_filter($field['options'] ?? []))) {
                session()->flash('error', 'Checkbox hoặc Radio phải có ít nhất một lựa chọn.');
                return;
            }
        }
        if ($this->action === 'CREATE') {
            $maxOrder = FieldForm::where('form_id', $this->type_of_form)->max('order') ?? 0;

            foreach ($this->customFields as $field) {
                $exists = FieldForm::where('form_id', $this->type_of_form)
                    ->where('label', $field['label'])
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'Tên trường đã tồn tại!');
                    return;
                }
                FieldForm::create([
                    'form_id' => $this->type_of_form,
                    'data_type' => $field['key'] ?? 'text',
                    'label' => $field['label'],
                    'options' => in_array($field['key'], ['checkbox', 'radio']) ? ($field['options'] ?? []) : null,
                    'order' => ++$maxOrder,
                ]);
            }
        }

        if ($this->action === 'UPDATE' && $this->fieldID) {
            $fieldDB = FieldForm::find($this->fieldID);

            if ($fieldDB) {
                $fieldDB->update([
                    'data_type' => $this->customFields[0]['key'] ?? 'text',
                    'label' => $this->customFields[0]['label'],
                    'options' => in_array($this->customFields[0]['key'], ['checkbox', 'radio'])
                        ? json_encode($this->customFields[0]['options'] ?? [])
                        : null,
                ]);
            }
        }

        $this->resetFieldState();
        $this->dispatch('$refresh');
    }

    public function resetFieldState()
    {
        $this->customFields = [];
        $this->action = 'CREATE';
        $this->fieldID = null;
    }

    public function deleteCustomField($id)
    {
        FieldForm::findOrFail($id)->delete();
    }

    // Thêm 1 option vào custom field
    public function addOption($index)
    {
        if (!isset($this->customFields[$index]['options'])) {
            $this->customFields[$index]['options'] = [];
        }

        $this->customFields[$index]['options'][] = '';
    }

    public function removeOption($fieldIndex, $optIndex)
    {
        if (isset($this->customFields[$fieldIndex]['options'][$optIndex])) {
            unset($this->customFields[$fieldIndex]['options'][$optIndex]);
            $this->customFields[$fieldIndex]['options'] = array_values($this->customFields[$fieldIndex]['options']);
        }
    }

    public function ChooseDataType($value, $index)
    {
        // Nếu kiểu là checkbox hoặc radio thì đảm bảo có mảng options
        if (in_array($value, ['checkbox', 'radio'])) {
            if (!isset($this->customFields[$index]['options']) || !is_array($this->customFields[$index]['options'])) {
                $this->customFields[$index]['options'] = [''];
            }
        } else {
            // Nếu đổi về kiểu khác thì xóa options
            unset($this->customFields[$index]['options']);
        }

        // Cập nhật key
        $this->customFields[$index]['key'] = $value;
    }
}
