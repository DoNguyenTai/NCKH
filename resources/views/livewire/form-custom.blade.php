<div>
    @if (session('error'))
        <div class="bg-red-100 text-black-700 p-3 rounded mb-3">{{ session('error') }}</div>
    @endif

    <div class="bg-gray-100">
        <div class="flex flex-col  mx-3 mt-6 lg:flex-row">
            <div class="w-full lg:w-1/3 rounded-lg bg-white p-5 m-1">
                <h2 class="text-xl font-semibold mb-4">Tạo Đơn Học Vụ</h2>



                <!-- Form -->
                <form wire:submit.prevent="save">
                    <label class="block text-gray-700">Loại đơn:</label>
                    <select wire:change="ChoosetypeForm($event.target.value)" wire:model="type_of_form"
                        class="border rounded-md p-2 w-full mt-1">
                        <option value="">Chọn loại đơn</option>
                        @foreach ($typeOfForm as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>

                    <div class="mt-4">
                        <h3 class="font-semibold text-gray-700">Thông tin bổ sung:</h3>
                        @foreach ($customFields as $index => $field)
                            <div class="mt-2 p-3 bg-gray-50 rounded-lg"
                                wire:key="custom-field-{{ $field['uuid'] ?? $index }}">
                                <label class="block text-gray-700">Kiểu dữ liệu:</label>
                                <select wire:change="ChooseDataType($event.target.value, {{ $index }})"
                                    wire:model="customFields.{{ $index }}.key"
                                    class="border rounded-md p-2 w-full mt-1">
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="number">Number</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="radio">Radio</option>
                                </select>

                                <label class="block text-gray-700 mt-2">Tên trường:</label>
                                <input type="text" wire:model="customFields.{{ $index }}.label"
                                    class="border rounded-md p-2 w-full mt-1" placeholder="Nhập giá trị">
                                <!-- Nếu là checkbox hoặc radio thì cho phép nhập option -->
                                @if (in_array($field['key'], ['checkbox', 'radio']))
                                    <div class="mt-2">
                                        <label class="block text-gray-700">Tùy chọn (Options):</label>

                                        @foreach ($field['options'] ?? [] as $optIndex => $option)
                                            <div class="flex items-center gap-2 mt-1">
                                                <input type="text"
                                                    wire:model="customFields.{{ $index }}.options.{{ $optIndex }}"
                                                    class="border rounded-md p-2 w-full" placeholder="Nhập lựa chọn">

                                                <button type="button"
                                                    wire:click="removeOption({{ $index }}, {{ $optIndex }})"
                                                    class="text-red-600 hover:text-red-800 font-bold">
                                                    &times;
                                                </button>
                                            </div>
                                        @endforeach

                                        <button type="button" wire:click="addOption({{ $index }})"
                                            class="mt-2 text-blue-600 hover:underline">
                                            + Thêm lựa chọn
                                        </button>
                                    </div>
                                @endif

                                <button type="button" wire:click="removeCustomField({{ $index }})"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 mt-3 rounded">
                                    Xóa
                                </button>
                            </div>
                        @endforeach

                    </div>

                    <button type="button" wire:click="addCustomField('CREATE')"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 mt-4 rounded-lg w-full">
                        + Thêm Trường
                    </button>

                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 mt-4 rounded-lg w-full">
                        Lưu Đơn
                    </button>
                </form>
            </div>
            <div class="w-full lg:w-2/3 m-1 bg-white shadow-lg text-lg rounded-lg border border-gray-200">
                <h1 class="text-center text-2xl uppercase font-bold py-2">{{ $selectedForm->name ?? '' }}</h1>
                <div id="field_list">
                    @if (!empty($selectedForm))
                        @foreach ($selectedForm->fieldForm as $item)
                            <div wire:key="field-{{ $item->id }}" draggable="true"
                                data-field-id="{{ $item->id }}"
                                class=" field_item bg-gray-200 shadow-xl m-3 p-4 flex justify-between items-center">
                                <p>{{ $item->label }}</p>
                                <div class="flex gap-3">
                                    <button wire:click="editCustomField('UPDATE', {{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path
                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                            <path fill-rule="evenodd"
                                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                        </svg>
                                    </button>
                                    <button wire:click="deleteCustomField({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                            <path
                                                d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>
<script>
    const fieldList = document.getElementById('field_list');
    let draggedItem = null;

    // Thêm draggable vào tất cả item ngay từ đầu
    document.querySelectorAll('.field_item').forEach(item => {
        item.setAttribute('draggable', 'true');
    });

    fieldList.addEventListener('dragstart', e => {
        if (!e.target.classList.contains('field_item')) return;
        draggedItem = e.target;
        e.target.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    fieldList.addEventListener('dragover', e => {
        e.preventDefault();
        const afterElement = getDragAfterElement(e.clientY);
        const currentItem = document.querySelector('.dragging');

        if (afterElement) {
            fieldList.insertBefore(currentItem, afterElement);
        } else {
            fieldList.appendChild(currentItem);
        }
    });

    fieldList.addEventListener('dragend', async () => {
        draggedItem.classList.remove('dragging');

        // Gọi API cập nhật vị trí
        try {
            const updatedOrder = getCurrentOrder();
            const response = await updateTaskOrder(updatedOrder);
            console.log('Cập nhật thành công:', response);
        } catch (error) {
            console.error('Lỗi khi cập nhật:', error);
            // Có thể thêm hiển thị thông báo lỗi cho người dùng
        }
    });

    // Hàm lấy thứ tự hiện tại
    function getCurrentOrder() {
        return Array.from(fieldList.children).map((item, index) => ({
            id: item.dataset.fieldId || index, // Giả sử mỗi task có data-task-id
            position: index + 1
        }));
    }

    // Hàm gọi API
    async function updateTaskOrder(orderData) {
        // Lấy CSRF token từ meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const API_URL = 'http://nckh.local/field/update-order';

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken, // Cho Laravel
                },
                body: JSON.stringify({
                    order: orderData
                })
            });

            if (!response.ok) throw new Error('API request failed');
            return response.json();
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    }

    function getDragAfterElement(y) {
        const items = [...fieldList.querySelectorAll('.field_item:not(.dragging)')];

        return items.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return {
                    offset: offset,
                    element: child
                };
            } else {
                return closest;
            }
        }, {
            offset: Number.NEGATIVE_INFINITY
        }).element;
    }

    document.addEventListener('livewire:load', () => {
        console.log('Livewire ready');
        // Re-bind drag and drop nếu thật sự cần
    });
</script>
