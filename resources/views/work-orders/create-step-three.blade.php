@extends('adminlte::page')

@section('title', 'Seleccionar Productos')

@section('content_header')
    <h1>Seleccionar Productos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5>Crear OT - Productos</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="bs-stepper linear">
                    <div class="bs-stepper-header" role="tablist">
                        <div class="step" data-target="#step-one">
                            <button type="button" class="step-trigger" role="tab" aria-controls="step-one"
                                    id="step-one-trigger" aria-selected="false" disabled>
                                <span class="bs-stepper-circle">1</span>
                                <span class="bs-stepper-label">Paso 1</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#step-two">
                            <button type="button" class="step-trigger" role="tab" aria-controls="step-two"
                                    id="step-two-trigger" aria-selected="false" disabled>
                                <span class="bs-stepper-circle">2</span>
                                <span class="bs-stepper-label">Paso 2</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step active" data-target="#step-three">
                            <button type="button" class="step-trigger" role="tab" aria-controls="step-three"
                                    id="step-three-trigger" aria-selected="true">
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label">Paso 3</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#step-four">
                            <button type="button" class="step-trigger" role="tab" aria-controls="step-four"
                                    id="step-four-trigger" aria-selected="false" disabled>
                                <span class="bs-stepper-circle">4</span>
                                <span class="bs-stepper-label">Paso 4</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step" data-target="#step-five">
                            <button type="button" class="step-trigger" role="tab" aria-controls="step-five"
                                    id="step-five-trigger" aria-selected="false" disabled>
                                <span class="bs-stepper-circle">5</span>
                                <span class="bs-stepper-label">Paso 5</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    <form id="work-order-form" action="{{ route('work-orders.store-step-three') }}" method="POST">
                        @csrf
                        <div id="step-three" class="content active dstepper-block" role="tabpanel"
                             aria-labelledby="step-three-trigger">
                            <div class="form-group">
                                <label for="products">Productos</label>
                                <div class="table-responsive">
                                    <table id="products-table"
                                           class="table table-bordered table-striped dataTable dtr-inline">
                                        <thead>
                                        <tr>
                                            <th>Seleccionar</th>
                                            <th>SKU</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th>Descuento (%)</th>
                                            <th>Cantidad</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($products as $product)
                                            <tr>
                                                <td><input type="checkbox" name="products[{{ $product->id }}][id]"
                                                           value="{{ $product->id }}"></td>
                                                <td>{{ $product->sku }}</td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->price }}</td>
                                                <td><input type="number" name="products[{{ $product->id }}][discount]"
                                                           value="0" min="0" max="100" class="form-control" disabled></td>
                                                <td><input type="number" name="products[{{ $product->id }}][quantity]"
                                                           value="1" min="1" class="form-control" disabled></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Siguiente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-footer">
            Paso a paso para generar una OT - Recuerda Verificar e ingresar información
            correcta.
        </div>
    </div>
@stop

@section('footer')
    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay
        Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{
    PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var selectedProducts = {};

    $(document).ready(function() {
        var table = $('#products-table').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
            },
            paging: true,
            searching: true,
        });

        // Manejar la selección de checkboxes y cantidad de productos
        $('#products-table tbody').on('change', 'input[type="checkbox"]', function() {
            var row = $(this).closest('tr');
            var productId = row.find('input[type="checkbox"]').val();
            var quantityField = row.find('input[type="number"][name$="[quantity]"]');
            var discountField = row.find('input[type="number"][name$="[discount]"]');

            if (this.checked) {
                quantityField.prop('disabled', false);
                discountField.prop('disabled', false);
                selectedProducts[productId] = {
                    quantity: quantityField.val(),
                    discount: discountField.val()
                };
            } else {
                quantityField.prop('disabled', true);
                discountField.prop('disabled', true);
                delete selectedProducts[productId];
            }
        });

        // Manejar el cambio en la cantidad de productos
        $('#products-table tbody').on('change', 'input[type="number"]', function() {
            var row = $(this).closest('tr');
            var productId = row.find('input[type="checkbox"]').val();
            if (selectedProducts.hasOwnProperty(productId)) {
                var quantityField = row.find('input[type="number"][name$="[quantity]"]');
                var discountField = row.find('input[type="number"][name$="[discount]"]');
                selectedProducts[productId] = {
                    quantity: quantityField.val(),
                    discount: discountField.val()
                };
            }
        });

        // Manejar la paginación para mantener las selecciones
        table.on('draw', function() {
            table.rows().every(function() {
                var row = this.node();
                var checkbox = $(row).find('input[type="checkbox"]');
                var productId = checkbox.val();
                var quantityField = $(row).find('input[type="number"][name$="[quantity]"]');
                var discountField = $(row).find('input[type="number"][name$="[discount]"]');

                if (selectedProducts.hasOwnProperty(productId)) {
                    checkbox.prop('checked', true);
                    quantityField.prop('disabled', false).val(selectedProducts[productId].quantity);
                    discountField.prop('disabled', false).val(selectedProducts[productId].discount);
                } else {
                    checkbox.prop('checked', false);
                    quantityField.prop('disabled', true).val(1);
                    discountField.prop('disabled', true).val(0);
                }
            });
        });

        // Enviar todas las selecciones al servidor
        $('#work-order-form').on('submit', function() {
            Object.keys(selectedProducts).forEach(function(productId) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'products[' + productId + '][id]',
                    value: productId
                }).appendTo('#work-order-form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'products[' + productId + '][quantity]',
                    value: selectedProducts[productId].quantity
                }).appendTo('#work-order-form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'products[' + productId + '][discount]',
                    value: selectedProducts[productId].discount
                }).appendTo('#work-order-form');
            });
        });
    });
</script>
@stop
