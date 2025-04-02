<!-- Modal -->
<div class="modal fade" id="view3DModal" tabindex="-1" aria-labelledby="view3DModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view3DModalLabel">{{ __('3D Model Viewer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Your 3D model content goes here -->
                <div id="3d-model-container">
                    <!-- This could be a Three.js, Babylon.js, or other 3D viewer implementation -->
                    <p>3D Model will be displayed here</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="amaz_primary_btn2 style3" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>