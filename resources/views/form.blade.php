<!-- Google Font & Tailwind CSS -->
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Roboto', 'sans-serif'],
                },
                colors: {
                    googlePurple: '#673AB7',
                    formBg: '#f3f0ff',
                }
            }
        }
    }
</script>

<!-- Wrapper -->
<div class="min-h-screen bg-gradient-to-br from-[#ede7f6] via-[#f3f0ff] to-[#e8eaf6] flex items-start justify-center py-16 px-4 font-sans">
    <div class="w-full max-w-3xl bg-white shadow-xl rounded-xl p-8 border border-gray-200">
        <!-- Title -->
        <div class="mb-6 border-b pb-3">
            <h2 class="text-3xl font-bold text-gray-800">{{ $typeOfForm->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Biểu mẫu theo phong cách Google Forms</p>
        </div>

        <!-- Success message -->
        @if (session()->has('success'))
            <div class="mb-5 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('form.submit', $typeOfForm->id) }}" method="POST" class="space-y-8">
            @csrf
            @foreach ($typeOfForm->fieldForm as $field)
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition">
                    <label class="block text-gray-800 font-medium mb-2">{{ $field->label }}</label>

                    @php
                        $inputBaseClass = 'w-full bg-transparent border-b-2 border-gray-300 focus:border-googlePurple outline-none py-1.5 text-gray-800 placeholder-gray-400 transition';
                        $textareaClass = 'w-full bg-transparent border-b-2 border-gray-300 focus:border-googlePurple outline-none py-1.5 text-gray-800 placeholder-gray-400 transition resize-none';
                        $selectClass = 'w-full border-b-2 border-gray-300 focus:border-googlePurple bg-transparent outline-none py-1.5 text-gray-800 transition';
                        $options = is_array($field->options) ? $field->options : json_decode($field->options ?? '[]', true);
                    @endphp

                    @switch($field->data_type)
                        @case('text')
                        @case('email')
                        @case('number')
                            <input type="{{ $field->data_type }}" name="field_{{ $field->id }}"
                                class="{{ $inputBaseClass }}" required>
                            @break

                        @case('textarea')
                            <textarea rows="3" name="field_{{ $field->id }}" class="{{ $textareaClass }}" required></textarea>
                            @break

                        @case('checkbox')
                            @foreach ($options as $opt)
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" name="field_{{ $field->id }}[]" value="{{ $opt }}"
                                        class="text-googlePurple focus:ring-googlePurple">
                                    <label class="text-gray-700">{{ $opt }}</label>
                                </div>
                            @endforeach
                            @break

                        @case('radio')
                            @foreach ($options as $opt)
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="radio" name="field_{{ $field->id }}" value="{{ $opt }}"
                                        class="text-googlePurple focus:ring-googlePurple">
                                    <label class="text-gray-700">{{ $opt }}</label>
                                </div>
                            @endforeach
                            @break

                        @case('select')
                            <select name="field_{{ $field->id }}" class="{{ $selectClass }}" required>
                                <option disabled selected>-- Chọn --</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break
                    @endswitch
                </div>
            @endforeach

            <!-- Submit -->
            <div class="text-right">
                <button type="submit"
                    class="bg-googlePurple text-white font-medium px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                    Gửi
                </button>
            </div>
        </form>
    </div>
</div>
