@extends('layouts.app')

@section('content')
    <div class="px-5">
        <form method="GET" action="{{ route('products.index') }}">
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Select gender</option>
                    @foreach($filters['gender'] as $gender)
                        <option value="{{ $gender }}" {{ request('gender') === $gender ? 'selected' : '' }}>
                            {{ ucfirst($gender) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control">
                    <option value="">Select Category</option>
                    @foreach($filters['category'] as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="search">Search Brand</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}"
                    placeholder="Enter brand name">
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
        <div class="mt-3">
            {{ $products->links() }}
        </div>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Brand</th>
                    <th>Images</th>
                    <th>Description</th>
                    <th>Detailed Description</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Gender</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>

                        <td class="w-25">
                            @if (!empty($product['images']))
                                @foreach ($product['images'] as $image)
                                    <a href="{{ $image }}" target="_blank">
                                        <img src="{{ $image }}" alt="{{ $product['name'] }}"
                                            style="width: 100px; height: auto; margin-right: 5px; margin-bottom: 5px; box-shadow: 0px 0px 2px 2px rgba(0,0,0,0.2);">
                                    </a>
                                @endforeach
                            @else
                                <span>No images available</span>
                            @endif
                        </td>

                        <td>{{ $product['product_description'] }}</td>
                        <td>{{ $product['detailed_description'] }}</td>
                        <td>{{ $product['price'] }}</td>
                        <td>{{ $product['category'] }}</td>
                        <td>{{ ucfirst($product['gender']) }}</td>

                        <td>
                            <form action="{{ route('products.sendToShopify', $loop->index) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">Send to Shopify</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const genderSelect = document.getElementById('gender');
                const categorySelect = document.getElementById('category');

                genderSelect.addEventListener('change', function () {
                    const selectedGender = this.value;

                    fetch('{{ route('products.getCategories') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ gender: selectedGender })
                    })
                        .then(response => response.json())
                        .then(categories => {
                            // clear old categories
                            categorySelect.innerHTML = '<option value="">Select Category</option>';

                            // add new categories
                            categories.forEach(category => {
                                const option = document.createElement('option');
                                option.value = category;
                                option.textContent = category;
                                categorySelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching categories:', error);
                        });
                });
            });
        </script>
    @endpush
@endsection