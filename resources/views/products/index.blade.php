@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Product Panel</h1>

        <form method="GET" action="{{ route('products.index') }}">
            <div class="form-group">
                <label for="gender">Gender</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Select gender</option>
                    @foreach($filters['gender'] as $gender)
                        <option value="{{ $gender }}" {{ request('gender') === $gender ? 'selected' : '' }}>{{ ucfirst($gender) }}
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

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Name</th>
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

                        <td>
                            @if (!empty($product['images']))
                                @foreach ($product['images'] as $image)
                                    <a href="{{ $image }}" target="_blank">
                                        <img src="{{ $image }}" alt="{{ $product['name'] }}"
                                            style="width: 50px; height: auto; margin-right: 5px;">
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
    </div>
@endsection