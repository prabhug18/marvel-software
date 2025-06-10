@foreach($products as $i => $productVal)
<tr>
    <td data-title="S.NO">{{ ($products->firstItem() ?? 0) + $i }}</td>
    <td data-title="CATEGORY">{{ $productVal->category->name }}</td>
    <td data-title="PRODUCT NAME">{{ $productVal->brand->name }}</td>
    <td data-title="MODEL NO">{{ $productVal->model }}</td>
    <td data-title="PRICE">{{ $productVal->price }}</td>
    <td class="action-buttons"> 
        <a class="btn btn-sm btn-outline-primary me-1"  href="{{ route('products.edit',$productVal->id) }}"  style="text-decoration: none;"><i class="fas fa-edit"></i></a>                  
        <form method="POST" action="{{ route('products.destroy', $productVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
        </form>
        <script>
            function ConfirmDelete()
            {
                var x = confirm("Are you sure you want to delete?");
                if (x)
                    return true;
                else
                    return false;
            }
        </script>
    </td>
</tr>
@endforeach
