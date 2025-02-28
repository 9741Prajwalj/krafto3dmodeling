<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Modal Page</title>
    <!-- <link rel="stylesheet"  type="text/css" href="{{ asset(path: 'css/threed.css') }}"> -->
    <link rel="stylesheet" type="text/css" href="{{ url('css/threed.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script async src="https://unpkg.com/es-module-shims@1.3.6/dist/es-module-shims.js"></script>
    <script type="importmap">
        {
            "imports": {
                "three": "https://unpkg.com/three@0.138.0/build/three.module.js",
                "three/addons/": "https://unpkg.com/three@0.138.0/examples/jsm/"
            }
        }
    </script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src='https://unpkg.co/gsap@3/dist/gsap.min.js'></script>
    <script src='https://unpkg.co/gsap@3/dist/ScrollTrigger.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.152.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.141.0/examples/js/controls/TransformControls.js"></script>
    <script type="module" src="{{ asset('js/custom3d.js') }}"></script>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="customizeModal" aria-labelledby="customizeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customizeModalLabel">Customize Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-panels">
                        <!-- Left Panel -->
                        <div class="modal-panel-left">
                            <div class="content11">
                                <div class="page3d">
                                    <div class="container3d">
                                        <canvas id="box-canvas"></canvas>
                                        <div class="ui-controls">
                                            <button class="unbutton ui-controls__button" id="zoom-in" aria-label="Zoom in">+</button>
                                            <button class="unbutton ui-controls__button" id="zoom-out" aria-label="Zoom out">-</button>
                                            <div>Scroll to animate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Right Panel -->
                        <div class="customization-container">
                            <div class="button-group">
                                <button class="btn upload" id="upload-btn">Upload</button>
                                <button class="btn reset" id="reset-btn">Reset</button>
                                <button class="btn rotate" id="rotate-btn">Rotate</button>
                                <button class="btn close" id="close-box-btn">Open Box</button>
                                <button class="btn open" id="open-box-btn">Close Box</button>
                            </div>
                            
                            <div class="button-group">
                                <button class="b1">Front</button>
                                <button class="b2">Left</button>
                                <button class="b3">Back</button>
                                <button class="b4">Right</button>
                                <button class="b5">Top</button>
                                <button class="b6">Bottom</button>
                            </div>
                            
                            <input type="text" class="text-input" placeholder="Add your text here">
                            
                            <div class="dropdown-group">
                                <label for="fontStyle"><u>Add more text</u></label>
                                <select id="fontStyle" class="dropdown">
                                    <option value="Decorative">Decorative</option>
                                </select>
                                <select id="fontSize" class="dropdown">
                                    <option value="20px">20px</option>
                                </select>
                            </div>
                            
                            <div class="printing-options">
                                <p>Choose your Printing type</p>
                                <div class="options-container">
                                    <div class="option flexo">
                                        <label for="flexo"> <input type="radio" id="flexo" name="printType"> Flexo</label>
                                        <ul>
                                            <li>1 Colour: No Bleed</li>
                                            <li>Basic Graphics</li>
                                            <li>3 Days</li>
                                            <li>Min Qty: 100</li>
                                        </ul>
                                        <div class="ink-colour-selector" style="display: none;">
                                            <p>Ink Colour:</p>
                                            <select id="inkColour">
                                                <option value="">-- Please Select --</option>
                                                <option value="black">Black</option>
                                                <option value="blue">Blue</option>
                                                <option value="red">Red</option>
                                                <option value="green">Green</option>
                                                <option value="white">White</option>
                                                <option value="silver">Metallic Silver</option>
                                                <option value="gold">Metallic Gold</option>
                                                <option value="two-colour">Two Colour (email us)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="option offset">
                                        <label for="offset"> <input type="radio" id="offset" name="printType" checked> Offset</label>
                                        <ul>
                                            <li>Full Bleed CMYK</li>
                                            <li>Multicolour Graphics</li>
                                            <li>10 Days</li>
                                            <li>Min Qty: 1000</li>
                                        </ul>
                                        <p class="setup-charge">Setup Charge: <span class="price">7999</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="image-preview">
                                <img src="{{ asset('images/flexo_image.png') }}" alt="Flexo Printing" width="550px" height="180px">
                            </div>
                            
                            <div class="pricing">
                                <span class="quantity">3000</span>
                                <span class="price">₹12.44</span>
                                <button class="btn add-to-cart">ADD TO CART</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const options = document.querySelectorAll(".option");
    const flexoRadio = document.getElementById("flexo");
    const inkSelector = document.querySelector(".ink-colour-selector");

    options.forEach(option => {
        option.addEventListener("click", function () {
            // Remove 'selected' class from all options
            options.forEach(opt => opt.classList.remove("selected"));

            // Add 'selected' class to the clicked option
            this.classList.add("selected");
        });
    });

    flexoRadio.addEventListener("change", function () {
        if (flexoRadio.checked) {
            inkSelector.style.display = "block";
        }
    });

    document.getElementById("offset").addEventListener("change", function () {
        inkSelector.style.display = "none";
    });
});

</script>
</html>