@extends('user_navbar')
@section('content')

{{-- Store Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Bank</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="storeBank" action="{{route('storeBank')}}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="" class="form-label"> Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Name" required>
                        </div>



                        <div class="mb-1">
                            <label for="" class="form-label">Account Number</label>
                            <input type="text" class="form-control" name="account_no" placeholder="Enter Account Number"
                                required>
                        </div>
                        <div class="mb-1">
                            <label for="" class="form-label">Branch</label>
                            <input type="text" class="form-control" name="branch" placeholder="Enter Branch Name">
                        </div>
                        <div class="mb-1">
                            <label for="" class="form-label">IBAN</label>
                            <input type="text" class="form-control" name="iban" placeholder="Enter IBAN">
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
                <h5 class="modal-title" id="exampleModalLabel">Edit Accessory</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="bankModal" action="{{route('updateBank')}}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-body">

                        <div class="mb-1">
                            <label for="mobile_name" class="form-label">Bank Name</label>
                            <input class="form-control" type="hidden" name="id" id="id" value="Update">
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-1">
                            <label for="" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_no" name="account_no"
                                placeholder="Enter Account Number" required>
                        </div>

                        <div class="mb-1">
                            <label for="" class="form-label">Branch</label>
                            <input type="text" class="form-control" id="branch" name="branch"
                                placeholder="Enter Branch Name">
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
                <i class="bi bi-plus"></i> Add Bank
            </button>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1 ">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Banks</h4>

                    </div>
                    <div class="table-responsive">
                        <table id="bankTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Name</th>
                                    <th>Account No</th>
                                    <th>Branch</th>
                                    <th>IBAN</th>
                                    <th>swift</th>




                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($banks as $bank)
                                <tr>
                                    <td>{{ $bank->created_at }}</td>
                                    <td>{{ $bank->name }}</td>
                                    <td>{{ $bank->account_no }}</td>
                                    <td>{{ $bank->branch }}</td>
                                    <td>{{ $bank->iban }}</td>
                                    <td>{{ $bank->swift }}</td>


                                    <td>
                                        <a href="" onclick="edit({{ $bank->id }})" data-toggle="modal"
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

<script>
    $(document).ready(function () {
        $('#bankTable').DataTable({
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
        url: '/getbank/' + id,
        success: function (data) {
        $("#bankModal").trigger("reset");
        
        $('#id').val(data.result.id);
        $('#name').val(data.result.name);
        $('#account_no').val(data.result.account_no);
        $('#branch').val(data.result.branch);
     
       
        
        },
        error: function (error) {
        console.log('Error:', error);
        }
        });
        }
       
</script>

@endsection