@foreach($products as $i => $productVal)
<tr>
    <td data-title="S.NO">{{ ($products->firstItem() ?? 0) + $i }}</td>
    <td data-title="CATEGORY">{{ $productVal->category->name }}</td>
    <td data-title="PRODUCT NAME">{{ $productVal->brand->name }}</td>
    <td data-title="MODEL NO">{{ $productVal->model }}</td>
    <td data-title="PRICE">{{ $productVal->price }}</td>
    <td data-title="OFFER PRICE">{{ $productVal->offer_price !== null ? rtrim(rtrim(number_format($productVal->offer_price, 2, '.', ''), '0'), '.') : '' }}</td>
    <td data-title="SPECIFICATION">{{ $productVal->specification ?? '-' }}</td>
    <td class="action-buttons"> 
        <a class="btn btn-sm btn-outline-primary me-1" href="{{ auth()->user() && auth()->user()->hasRole('Admin') ? route('products.edit',$productVal->id) : '#' }}" style="text-decoration: none; @if(!auth()->user() || !auth()->user()->hasRole('Admin')) pointer-events: none; opacity: 0.6; @endif" @if(!auth()->user() || !auth()->user()->hasRole('Admin')) tabindex="-1" aria-disabled="true" title="Only admin can edit" @endif><i class="fas fa-edit"></i></a>
        @if(auth()->user() && auth()->user()->hasRole('Admin'))
            <form method="POST" action="{{ route('products.destroy', $productVal->id) }}" class="btn" onsubmit="return ConfirmDelete()">
                @csrf
                @method('DELETE')
                @if($productVal->invoices_count > 0)
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot delete: Product used in invoice">
                        <button type="submit" class="btn btn-sm btn-outline-danger" disabled style="pointer-events: none; opacity: 0.6;"><i class="fas fa-trash-alt"></i></button>
                    </span>
                @else
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                @endif
            </form>
        @endif
    </td>
</tr>
@endforeach
