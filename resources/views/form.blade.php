@foreach ($typeOfForm->fieldForm as $item)
    <input type="{{$item->data_type}}" placeholder="{{$item->value}}">
    
@endforeach