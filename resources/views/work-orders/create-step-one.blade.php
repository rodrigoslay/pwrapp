@extends('adminlte::page')

@section('title', 'Seleccionar Vehículo y Cliente')

@section('content_header')
    <h1>Crear OT - Seleccionar Vehículo y Cliente</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Crear OT - Vehículo y Cliente</h3>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-xs btn-success" data-toggle="modal"
                            data-target="#addClientModal">+Cliente</button>
                        <button type="button" class="btn btn-xs btn-primary" data-toggle="modal"
                            data-target="#addVehicleModal">+Vehículo</button>
                        <button type="button" class="btn btn-xs btn-info" data-toggle="modal"
                            data-target="#addBrandModal">+Marca</button>
                        <button type="button" class="btn btn-xs btn-warning" data-toggle="modal"
                            data-target="#addModelModal">+Modelo</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="bs-stepper linear">
                        <div class="bs-stepper-header" role="tablist">
                            @foreach (range(1, 5) as $step)
                                <div class="step {{ $step == 1 ? 'active' : '' }}" data-target="#step-{{ $step }}">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-{{ $step }}" id="step-{{ $step }}-trigger"
                                        aria-selected="{{ $step == 1 ? 'true' : 'false' }}"
                                        {{ $step != 1 ? 'disabled' : '' }}>
                                        <span class="bs-stepper-circle">{{ $step }}</span>
                                        <span class="bs-stepper-label">Paso {{ $step }}</span>
                                    </button>
                                </div>
                                @if ($step < 5)
                                    <div class="line"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="bs-stepper-content">
                            <form action="{{ route('work-orders.search-vehicle') }}" method="POST">
                                @csrf
                                <div id="step-one" class="content active dstepper-block" role="tabpanel"
                                    aria-labelledby="step-one-trigger">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vehicle_id">Patente del Vehículo</label>
                                                <select name="vehicle_id" id="vehicle_id" class="form-control select2"
                                                    required>
                                                    <option value="">Seleccione una opción</option>
                                                    @foreach ($vehicles as $vehicle)
                                                        <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="client_id">Cliente (RUT)</label>
                                                <select name="client_id" id="client_id" class="form-control select2"
                                                    required>
                                                    <option value="">Seleccione una opción</option>
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}"
                                                            data-name="{{ $client->name }}">{{ $client->rut }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="order_type"
                                                            id="create_ot" value="create_ot" checked>
                                                        <label class="form-check-label" for="create_ot">Crear OT</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="order_type"
                                                            id="cotizacion" value="cotizacion">
                                                        <label class="form-check-label" for="cotizacion">Cotización</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="order_type"
                                                            id="agendar" value="agendar">
                                                        <label class="form-check-label" for="agendar">Agendar</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input type="datetime-local" id="scheduling" name="scheduling"
                                                            class="form-control" placeholder="Fecha y hora"
                                                            style="display:none;" min="{{ now()->format('Y-m-d\TH:i') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Siguiente</button>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h5>Detalle del Vehículo</h5>
                                                    <div id="vehicle_detail">
                                                        <label for="vehiculo_marca_text">Marca:</label>
                                                        <span id="vehiculo_marca_text" class="mb-1"></span><br>
                                                        <label for="vehiculo_modelo_text">Modelo:</label>
                                                        <span id="vehiculo_modelo_text" class="mb-1"></span><br>
                                                        <label for="vehiculo_chasis_text">Chasis:</label>
                                                        <span id="vehiculo_chasis_text" class="mb-1"></span><br>
                                                        <label for="vehiculo_kilometros_text">Kilómetros:</label>
                                                        <span id="vehiculo_kilometros_text" class="mb-1"></span><br>
                                                        <label for="vehiculo_color_text">Color:</label>
                                                        <span id="vehiculo_color_text" class="mb-1"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5>Detalle del Cliente</h5>
                                                    <div id="client_detail">
                                                        <label for="client_name_text">Nombre:</label>
                                                        <span id="client_name_text" class="mb-1"></span><br>
                                                        <label for="client_rut_text">Rut:</label>
                                                        <span id="client_rut_text" class="mb-1"></span><br>
                                                        <label for="client_email_text">Email:</label>
                                                        <span id="client_email_text" class="mb-1"></span><br>
                                                        <label for="client_telefono_text">Teléfono:</label>
                                                        <span id="client_telefono_text" class="mb-1"></span><br>
                                                        <label for="client_grupocliente_text">Grupo de Cliente:</label>
                                                        <span id="client_grupocliente_text" class="mb-1"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h5>Imagen del Vehículo</h5>
                                                    <img id="vehicle_image" src="" alt="Imagen del Vehículo"
                                                        class="img-fluid">
                                                    <p id="no_image_message" class="d-none">El vehículo no tiene foto. <a
                                                            href="#" id="add_image_link">Haz clic aquí</a> para
                                                        agregar una.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    Paso a paso para generar una OT - Recuerda Verificar e ingresar información correcta.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar cliente -->
    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="add-client-form" action="{{ route('clients.store') }}" method="POST" class="ajax-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addClientModalLabel">Agregar Cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="rut">RUT</label>
                            <input type="text" name="rut" id="rut" class="form-control" required>
                            <small id="rut_feedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                            <small id="email_feedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" name="phone" id="phone" class="form-control" required>
                            <small id="phone_feedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label for="client_group_id">Grupo de Cliente</label>
                            <select name="client_group_id" id="client_group_id" class="form-control select2" required>
                                @foreach ($clientGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select name="status" id="status" class="form-control select2" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal para agregar vehículo -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1" role="dialog" aria-labelledby="addVehicleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="add-vehicle-form" action="{{ route('vehicles.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addVehicleModalLabel">Agregar Vehículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="license_plate">Patente</label>
                            <input type="text" name="license_plate" id="license_plate" class="form-control" required>
                            <small id="license_plate_feedback" class="form-text"></small>
                        </div>
                        <div class="form-group">
                            <label for="brand_id">Marca</label>
                            <select name="brand_id" id="brand_id" class="form-control select2" required>
                                <option value="">Seleccione una opción</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="model">Modelo</label>
                            <select name="model" id="model" class="form-control select2" required>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" name="color" id="color" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label for="chassis">Chasis</label>
                            <input type="text" name="chassis" id="chassis" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label for="kilometers">Kilómetros</label>
                            <input type="number" name="kilometers" id="kilometers" class="form-control"value="0">
                        </div>
                        <div class="form-group">
                            <label for="registration_date">Fecha de Registro</label>
                            <input type="date" name="registration_date" id="registration_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="photo">Foto del Vehículo</label>
                            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="client_id_vehicle">Cliente (RUT)</label>
                            <select name="client_id_vehicle" id="client_id_vehicle" class="form-control select2"
                                required>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->rut }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Vehículo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para agregar marca -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" role="dialog" aria-labelledby="addBrandModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="add-brand-form" action="{{ route('brands.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBrandModalLabel">Agregar Marca</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nombre de la Marca</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select name="status" id="status" class="form-control select2" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Marca</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para agregar modelo -->
    <div class="modal fade" id="addModelModal" tabindex="-1" role="dialog" aria-labelledby="addModelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="add-model-form" action="{{ route('car-models.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModelModalLabel">Agregar Modelo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="brand_id_for_model">Marca</label>
                            <select name="brand_id" id="brand_id_for_model" class="form-control select2" required>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="model">Nombre del Modelo</label>
                            <input type="text" name="model" id="model" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Año</label>
                            <input type="number" name="year" id="year" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Modelo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }

        #vehicle_image {
            display: none;
        }

        #no_image_message {
            display: none;
        }
    </style>
@stop

@section('js')
    <script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para validar el RUT
        // Función para validar el RUT
        function validarRUT(rut) {
            rut = rut.replace(/\./g, '').replace('-', '').toUpperCase();
            let numero = rut.slice(0, -1);
            let digitoVerificador = rut.slice(-1);
            if (!/^\d+$/.test(numero)) {
                return false;
            }
            let suma = 0;
            let multiplicador = 2;
            for (let i = numero.length - 1; i >= 0; i--) {
                suma += parseInt(numero.charAt(i)) * multiplicador;
                multiplicador = multiplicador < 7 ? multiplicador + 1 : 2;
            }
            let dvCalculado = 11 - (suma % 11);
            if (dvCalculado === 11) {
                dvCalculado = '0';
            } else if (dvCalculado === 10) {
                dvCalculado = 'K';
            } else {
                dvCalculado = dvCalculado.toString();
            }
            return digitoVerificador === dvCalculado;
        }


        // Función para validar la Patente
        function validarPatente(patente) {
            const formatoAntiguo = /^[A-Z]{2}[0-9]{4}$/;
            const formatoNuevo = /^[A-Z]{4}[0-9]{2}$/;
            patente = patente.toUpperCase();
            return formatoAntiguo.test(patente) || formatoNuevo.test(patente);
        }

        // Función para transformar el texto a la primera letra de cada palabra en mayúscula y las siguientes en minúsculas
        function capitalizarPrimeraLetra(texto) {
            return texto.split(' ').map(function(palabra) {
                return palabra.charAt(0).toUpperCase() + palabra.slice(1).toLowerCase();
            }).join(' ');
        }
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            @if ($errors->any())
                Swal.fire({
                    icon: '{{ session('alert.type') }}',
                    title: '{{ session('alert.title') }}',
                    text: '{{ session('alert.message') }}',
                });
            @endif

            $('#vehicle_id').on('change', function() {
                var vehicleId = $(this).val();
                if (vehicleId) {
                    $.get('/vehicles/' + vehicleId, function(vehicle) {
                        $('#vehiculo_marca_text').text(vehicle.brand.name);
                        $('#vehiculo_modelo_text').text(vehicle.model);
                        $('#vehiculo_chasis_text').text(vehicle.chassis);
                        $('#vehiculo_kilometros_text').text(vehicle.kilometers);
                        $('#vehiculo_color_text').text(vehicle.color);
                        if (vehicle.photo) {
                            $('#vehicle_image').attr('src', '/img/vehicles/' + vehicle.photo)
                                .show();
                            $('#no_image_message').hide();
                        } else {
                            $('#vehicle_image').hide();
                            $('#no_image_message').show();
                        }
                    }).fail(function() {
                        alert('Error al cargar los detalles del vehículo.');
                    });
                }
            });

            $('#client_id').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    $.get('/clients/' + clientId, function(client) {
                        $('#client_name_text').text(client.name);
                        $('#client_rut_text').text(client.rut);
                        $('#client_email_text').text(client.email);
                        $('#client_telefono_text').text(client.phone);
                        $('#client_grupocliente_text').text(client.client_group.name);
                    }).fail(function() {
                        alert('Error al cargar los detalles del cliente.');
                    });
                }
            });

            $('#brand_id').on('change', function() {
                var brandId = $(this).val();
                if (brandId) {
                    $.get('/brands/' + brandId + '/models', function(response) {
                        if (response.success) {
                            var select = $('#model');
                            select.empty();
                            select.append('<option value="">Seleccione una opción</option>');
                            $.each(response.models, function(index, model) {
                                select.append('<option value="' + model.model + '">' + model
                                    .model + ' - ' + model.year + '</option>');
                            });
                            select.trigger('change');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                            });
                        }
                    }).fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar los modelos de la marca seleccionada.',
                        });
                    });
                }
            });

            $('#license_plate').on('input', function() {
                var patente = $(this).val().toUpperCase();
                $(this).val(patente);
                if (validarPatente(patente)) {
                    $('#license_plate_feedback').text('La patente es válida.').css('color', 'green');
                } else {
                    $('#license_plate_feedback').text('La patente no es válida.').css('color', 'red');
                }
            });

            $('#rut').on('input', function() {
                var rut = $(this).val().replace(/\./g, '').replace('-', '').toUpperCase();
                $(this).val(rut);
                if (validarRUT(rut)) {
                    $('#rut_feedback').text('El RUT es válido.').css('color', 'green');
                } else {
                    $('#rut_feedback').text('El RUT no es válido.').css('color', 'red');
                }
            });

            $('#phone').on('input', function() {
                var phone = $(this).val();
                if (/^56[29][0-9]{8}$/.test(phone)) {
                    $('#phone_feedback').text('El teléfono es válido.').css('color', 'green');
                } else {
                    $('#phone_feedback').text('El teléfono no es válido.').css('color', 'red');
                }
            });

            $('#email').on('input', function() {
                var email = $(this).val();
                var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                if (emailPattern.test(email)) {
                    $('#email_feedback').text('El email es válido.').css('color', 'green');
                } else {
                    $('#email_feedback').text('El email no es válido.').css('color', 'red');
                }
            });

            $('#add_image_link').on('click', function(e) {
                e.preventDefault();
                $('#addVehicleModal').modal('show');
            });

            $('#addVehicleModal').on('shown.bs.modal', function() {
                var registrationDateInput = $('#registration_date');
                if (!registrationDateInput.val()) {
                    var defaultDate = '0000-00-00';
                    registrationDateInput.val(defaultDate);
                }
            });

            $('#add-client-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#addClientModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Cliente agregado',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            var errors = response.responseJSON.errors;
                            var errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value[0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                html: errorMessages,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.responseJSON.message,
                            });
                        }
                    }
                });
            });

            $('#add-vehicle-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            $('#addVehicleModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Vehículo agregado',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });
                            $.ajax({
                                type: 'GET',
                                url: '{{ route('vehicles.list') }}',
                                success: function(data) {
                                    var select = $('#vehicle_id');
                                    select.empty();
                                    $.each(data.vehicles, function(index, vehicle) {
                                        select.append('<option value="' +
                                            vehicle.id + '">' + vehicle
                                            .license_plate + '</option>'
                                        );
                                    });
                                }
                            });
                        }
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            var errors = response.responseJSON.errors;
                            var errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value[0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                html: errorMessages,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.responseJSON.message ||
                                    'Error al agregar el vehículo.',
                            });
                        }
                    }
                });
            });

            $('#add-brand-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#addBrandModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Marca agregada',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });
                            $.ajax({
                                type: 'GET',
                                url: '{{ route('brands.list') }}',
                                success: function(data) {
                                    var select = $('#brand_id');
                                    select.empty();
                                    var brands = data.brands.sort(function(a, b) {
                                        return a.name.localeCompare(b.name);
                                    });
                                    $.each(brands, function(index, brand) {
                                        select.append('<option value="' +
                                            brand.id + '">' + brand
                                            .name + '</option>');
                                    });
                                }
                            });
                        }
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            var errors = response.responseJSON.errors;
                            var errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value[0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                html: errorMessages,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.responseJSON.message,
                            });
                        }
                    }
                });
            });

            $('#add-model-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#addModelModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Modelo agregado',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            var errors = response.responseJSON.errors;
                            var errorMessages = '';
                            $.each(errors, function(key, value) {
                                errorMessages += value[0] + '<br>';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                html: errorMessages,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.responseJSON.message,
                            });
                        }
                    }
                });
            });

            $('input[name="order_type"]').on('change', function() {
                if ($(this).val() === 'agendar') {
                    $('#scheduling').show();
                } else {
                    $('#scheduling').hide();
                }
            });

            $('#name').on('input', function() {
                $(this).val(capitalizarPrimeraLetra($(this).val()));
            });

        });
    </script>
@stop
