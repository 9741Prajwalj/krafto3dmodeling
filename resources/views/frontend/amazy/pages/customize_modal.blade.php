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
    <script src="https://cdn.jsdelivr.net/npm/three@0.152.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.141.0/examples/js/controls/TransformControls.js"></script>
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
                                <button class="btn1 upload" id="upload-btn">Upload</button>
                                <button class="btn2 reset" id="reset-btn">Reset</button>
                                <button class="btn3 rotate" id="rotate-btn">Rotate</button>
                                <button class="btn4 closes" id="close-btn-box">Open Box</button>
                                <button class="btn5 open" id="open-box-btn">Close Box</button>
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
                                <label for="fontStyle" id="addTextLink"><u>Add more text</u></label>
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
                                <button class="add-to-cart">ADD TO CART</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</body>
<script type="module" src="{{ asset('js/custom3d.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const options = document.querySelectorAll(".option");
    const flexoRadio = document.getElementById("flexo");
    const inkSelector = document.querySelector(".ink-colour-selector");

    options.forEach(option => {
        option.addEventListener("click", function () {
            options.forEach(opt => opt.classList.remove("selected"));
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

    function populateDropdowns() {
        const fontStyleSelect = document.getElementById("fontStyle");
        const fontSizeSelect = document.getElementById("fontSize");

        if (!fontStyleSelect || !fontSizeSelect) {
            console.error("Dropdown elements not found!");
            return;
        }

        const fontStyles = [
            { value: "Helvetica", text: "Helvetica" },
            { value: "Arial", text: "Arial" },
            { value: "Arial Black", text: "Arial Black" },
            { value: "Agency FB", text: "Agency FB" },
            { value: "Comic Sans MS", text: "Comic Sans MS" },
            { value: "Courier", text: "Courier" },
            { value: "Decorative", text: "Decorative" },
            { value: "Georgia", text: "Georgia" },
            { value: "Impact", text: "Impact" },
            { value: "Monospace", text: "Monospace" },
            { value: "Palatino", text: "Palatino" },
            { value: "Sans Serif", text: "Sans Serif" },
            { value: "Times New Roman", text: "Times New Roman" },
            { value: "Trebuchet MS", text: "Trebuchet MS" },
            { value: "Verdana", text: "Verdana" }
        ];

        fontStyleSelect.innerHTML = "";
        fontSizeSelect.innerHTML = "";

        fontStyles.forEach(font => {
            const option = document.createElement("option");
            option.value = font.value;
            option.textContent = font.text;
            if (font.value === "Georgia") {
                option.selected = true;
            }
            fontStyleSelect.appendChild(option);
        });

        for (let size = 2; size <= 45; size++) {
            const option = document.createElement("option");
            option.value = `${size}px`;
            option.textContent = `${size}px`;
            if (size === 6) {
                option.selected = true;
            }
            fontSizeSelect.appendChild(option);
        }
    }

    function toggleAdditionalInputs() {
        const addTextLink = document.getElementById("addTextLink");
        const dropdownGroup = document.querySelector(".dropdown-group");
        let additionalInputs = document.getElementById("additionalInputs");

        if (!addTextLink || !dropdownGroup) {
            console.error("Add text link or dropdown group not found!");
            return;
        }

        // Define the toggle logic
        const toggleInputs = function (e) {
            e.preventDefault();

            if (!additionalInputs) {
                // Create the additionalInputs container if it doesn't exist
                additionalInputs = document.createElement("div");
                additionalInputs.id = "additionalInputs";

                // Create exactly 3 input fields
                for (let i = 0; i < 3; i++) {
                    const input = document.createElement("input");
                    input.type = "text";
                    input.className = "text-input";
                    input.placeholder = "Add your text here";
                    input.style.marginTop = "10px";
                    additionalInputs.appendChild(input);
                }

                // Insert the additionalInputs div after the dropdown-group
                dropdownGroup.insertAdjacentElement("afterend", additionalInputs);
                addTextLink.textContent = "Remove text";
            } else {
                // Remove the additionalInputs div if it exists
                additionalInputs.remove();
                additionalInputs = null;
                addTextLink.textContent = "Add more text";
            }
        };

        // Add the event listener only once
        addTextLink.addEventListener("click", toggleInputs);
    }

    const fontStyleSelect = document.getElementById("fontStyle");
    const fontSizeSelect = document.getElementById("fontSize");

    fontStyleSelect.addEventListener("change", function() {
        console.log("Selected font style:", this.value);
    });

    fontSizeSelect.addEventListener("change", function() {
        console.log("Selected font size:", this.value);
    });

    populateDropdowns();
    toggleAdditionalInputs();

    console.log("Script loaded and executed");
});
</script>
</html>