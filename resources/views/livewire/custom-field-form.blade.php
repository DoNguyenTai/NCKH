<div class="max-w-lg mx-auto p-5 bg-white shadow rounded">
    <h2 class="text-xl font-bold mb-4">Tạo Câu Hỏi</h2>

    <div>
        <label class="block font-medium">Câu hỏi:</label>
        <input type="text" wire:model="question" class="border p-2 w-full">
    </div>

    <div class="mt-4">
        <h3 class="font-bold">Trường tùy chỉnh:</h3>
        @foreach($customFields as $index => $field)
            <div class="flex items-center gap-2 mt-2">
                <input type="text" wire:model="customFields.{{ $index }}.key" placeholder="Tên trường" class="border p-2 w-1/3">
                <input type="text" wire:model="customFields.{{ $index }}.value" placeholder="Giá trị" class="border p-2 w-2/3">
                <button wire:click="removeCustomField({{ $index }})" class="bg-red-500 text-white px-2">X</button>
            </div>
        @endforeach
    </div>

    <button wire:click="addCustomField" class="bg-blue-500 text-white px-3 py-1 mt-4">Thêm Trường</button>

    <button wire:click="save" class="bg-green-500 text-white px-3 py-1 mt-4">Lưu</button>

    @if (session()->has('message'))
        <p class="text-green-500 mt-3">{{ session('message') }}</p>
    @endif
</div>
