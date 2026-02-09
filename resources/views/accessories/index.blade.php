@extends('user_navbar')
@section('content')

{{-- Store Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Perfume</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="storeMobile" action="{{ route('accessories.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="mobile_name" class="form-label">Perfume Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label for="company_id" class="form-label">Company</label>
                            <select class="form-control" name="company_id" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-control" name="group_id" required>
                                <option value="">Select Group</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="pay_amount" class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description" placeholder="Enter Description">
                        </div>
                        <div class="mb-1">
                            <label for="min_qty" class="form-label">Minimum Quantity</label>
                            <input type="integer" class="form-control" name="min_qty"
                                placeholder="Enter Minumum Quantity" required>
                        </div>
                        <div class="mb-1">
                            <label for="picture" class="form-label">Perfume Image</label>
                            <input type="file" name="picture" id="picture" class="form-control" accept="image/*">

                            {{-- Preview box --}}
                            <div id="imagePreviewContainer" class="mt-2" style="display:none;">
                                <p class="mb-1">Preview:</p>
                                <img id="imagePreview" src="" alt="Perfume Preview"
                                    style="max-width: 120px; max-height: 120px; border: 1px solid #ddd; border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="storeButton">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
{{-- End Store Modal --}}

{{-- Edit Modal --}}

<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Perfume</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="editAccessory" action="{{ route('accessories.update') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="mobile_name" class="form-label">Accessory Name</label>
                            <input class="form-control" type="hidden" name="id" id="id" value="Update">
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label for="company_id" class="form-label">Company</label>
                            <select class="form-control" id="company_id" name="company_id" required>
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-control" id="group_id" name="group_id" required>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label for="min_qty" class="form-label">Minimum Quantity</label>
                            <input id="min_qty" class="form-control" name="min_qty" placeholder="Enter Minumum Quantity"
                                required>
                        </div>


                        <div class="mb-1">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" name="description" id="description">
                        </div>
                        <div class="mb-1">
                            <label for="password" class="form-label">Edit Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- End Edit Modal --}}


<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            @if (session('success'))
            <div class="alert alert-success" id="successMessage">
                {{ session('success') }}
            </div>
            @endif

            @if (session('danger'))
            <div class="alert alert-danger" id="dangerMessage" style="color: red;">
                {{ session('danger') }}
            </div>
            @endif


            <button type="button" class="btn btn-primary ml-1" data-toggle="modal" data-target="#exampleModal">
                <i class="bi bi-plus"></i> Add Perfume
            </button>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1 ">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Perfumes</h4>

                    </div>
                    <div class="table-responsive">
                        <table id="accessoryTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Created By</th>

                                    <th>Picture</th>
                                    <th>Name</th>
                                    <th>Group</th>
                                    <th>Company</th>
                                    <th>Remaining Qty</th>
                                    <th>Minimum Quantity</th>
                                    <th>Description</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accessories as $accessory)
                                <tr>
                                    <td>{{ $accessory->created_at }}</td>
                                    <td>{{ $accessory->user->name }}</td>
                                   <td>
                                    @if($accessory->picture)
                                    <img src="{{ asset('storage/' . $accessory->picture) }}" alt="Accessory Image" width="60" height="60"
                                        style="object-fit:cover; border-radius:6px; border:1px solid #ddd; cursor:pointer;"
                                        onclick="showFullImage('{{ asset('storage/' . $accessory->picture) }}')">
                                    @else
                                    <span class="text-muted">No Image</span>
                                    @endif
                                </td>
                                    <td>{{ $accessory->name }}</td>
                                    <td>{{ $accessory->group->name ?? '-' }}</td>
                                    <td>{{ $accessory->company->name ?? '-' }}</td>
                                    <td><strong>{{ $accessory->total_remaining }}</strong></td>
                                    <td>{{ $accessory->min_qty }}</td>
                                    <td>{{ $accessory->description }}</td>
                                    <td>
                                        <a href="" onclick="edit({{ $accessory->id }})" data-toggle="modal"
                                            data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Fullscreen Image Viewer -->
<div id="imageViewer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center;">
    <img id="viewerImage" src="" alt="Full-size"
        style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 0 20px #000;">
    <button onclick="closeImageViewer()" style="position:absolute; top:20px; right:40px; background:none; border:none;
                   color:white; font-size:40px; cursor:pointer;">&times;</button>
</div>

<script>

    function showFullImage(src) {
    const viewer = document.getElementById('imageViewer');
    const img = document.getElementById('viewerImage');
    img.src = src;
    viewer.style.display = 'flex';
    }
    
    function closeImageViewer() {
    document.getElementById('imageViewer').style.display = 'none';
    }
    
    // Optional: close when clicking outside the image
    document.getElementById('imageViewer').addEventListener('click', function(e) {
    if (e.target.id === 'imageViewer') closeImageViewer();
    });


    
    document.getElementById('picture').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    
    if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
    preview.src = e.target.result;
    previewContainer.style.display = 'block';
    };
    reader.readAsDataURL(file);
    } else {
    preview.src = '';
    previewContainer.style.display = 'none';
    }
    });


    $(document).ready(function () {
        $('#accessoryTable').DataTable({
        order: [
        [0, 'desc']
        ]
        });
        });

function edit(value) {
        console.log(value);
        var id = value;
        $.ajax({
        type: "GET",
        url: '/accessoryedit/' + id,
        success: function (data) {
        $("#editAccessory").trigger("reset");
        
        $('#id').val(data.result.id);
        $('#name').val(data.result.name);
        $('#company_id').val(data.result.company_id);
        $('#group_id').val(data.result.group_id);
        $('#description').val(data.result.description);
        $('#min_qty').val(data.result.min_qty);
        
       
        
        },
        error: function (error) {
        console.log('Error:', error);
        }
        });
        }
       
</script>

@endsection