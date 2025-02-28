import * as THREE from 'three';
import {OrbitControls} from 'three/addons/controls/OrbitControls.js';
import {mergeBufferGeometries} from 'three/addons/utils/BufferGeometryUtils.js';
import {GUI} from 'three/addons/libs/lil-gui.module.min.js';

// Check for gsap (loaded via HTML script tags)
if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    console.error('gsap or ScrollTrigger not loaded. Ensure script tags are present in HTML.');
}

const container = document.querySelector('.container3d');
const boxCanvas = document.querySelector('#box-canvas');
let textMesh = null; // Variable to store the text mesh

let box = {
    params: {
        width: 27,
        widthLimits: [15, 70],
        length: 80,
        lengthLimits: [40, 120],
        depth: 45,
        depthLimits: [15, 70],
        thickness: .6,
        thicknessLimits: [.1, 1],
        fluteFreq: 5,
        fluteFreqLimits: [3, 7],
        flapGap: 1,
        copyrightSize: [27, 10]
    },
    els: {
        group: new THREE.Group(),
        backHalf: {
            width: {
                top: new THREE.Mesh(),
                side: new THREE.Mesh(),
                bottom: new THREE.Mesh(),
            },
            length: {
                top: new THREE.Mesh(),
                side: new THREE.Mesh(),
                bottom: new THREE.Mesh(),
            },
        },
        frontHalf: {
            width: {
                top: new THREE.Mesh(),
                side: new THREE.Mesh(),
                bottom: new THREE.Mesh(),
            },
            length: {
                top: new THREE.Mesh(),
                side: new THREE.Mesh(),
                bottom: new THREE.Mesh(),
            },
        }
    },
    animated: {
        openingAngle: .02 * Math.PI,
        flapAngles: {
            backHalf: {
                width: {
                    top: 0,
                    bottom: 0
                },
                length: {
                    top: 0,
                    bottom: 0
                },
            },
            frontHalf: {
                width: {
                    top: 0,
                    bottom: 0
                },
                length: {
                    top: 0,
                    bottom: 0
                },
            }
        }
    }
};

// Globals
let renderer, scene, camera, orbit, lightHolder, rayCaster, mouse, copyright;

// Run the app
initScene();
createControls();
window.addEventListener('resize', updateSceneSize);

// --------------------------------------------------
// Three.js scene

function initScene() {

    renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: true,
        canvas: boxCanvas
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(45, (container.clientWidth) / container.clientHeight, 10, 1000);    
    camera.position.set(40, 90, 110);
    rayCaster = new THREE.Raycaster();
    mouse = new THREE.Vector2(0, 0);

    updateSceneSize();

    scene.add(box.els.group);
    setGeometryHierarchy();

    const ambientLight = new THREE.AmbientLight(0xffffff, .5);
    scene.add(ambientLight);
    lightHolder = new THREE.Group();
    const topLight = new THREE.PointLight(0xffffff, .5);
    topLight.position.set(-30, 300, 0);
    lightHolder.add(topLight);
    const sideLight = new THREE.PointLight(0xffffff, .7);
    sideLight.position.set(50, 0, 150);
    lightHolder.add(sideLight);
    scene.add(lightHolder);

    scene.add(box.els.group);
    setGeometryHierarchy();

    const material = new THREE.MeshStandardMaterial({
        color: new THREE.Color(0x9C8D7B),
        side: THREE.DoubleSide
    });
    box.els.group.traverse(c => {
        if (c.isMesh) c.material = material;
    });

    orbit = new OrbitControls(camera, boxCanvas);
    orbit.enableZoom = false;
    orbit.enablePan = false;
    orbit.enableDamping = true;
    orbit.autoRotate = false;
    orbit.autoRotateSpeed = .25;

    // initializeBoxScene();
    setupFaceViewControls();
    createCopyright();
    createBoxElements();
    createFoldingAnimation();
    createZooming();

    render();
}

function render() {
    orbit.update();
    lightHolder.quaternion.copy(camera.quaternion);
    
    renderer.render(scene, camera);
    requestAnimationFrame(render);
}

function updateSceneSize() {
    camera.aspect = (container.clientWidth) / container.clientHeight;
    camera.updateProjectionMatrix();

    renderer.setSize(container.clientWidth, container.clientHeight);
}

// End of Three.js scene
// --------------------------------------------------

// --------------------------------------------------
// Box geometries

function setGeometryHierarchy() {
    box.els.group.add(box.els.frontHalf.width.side, box.els.frontHalf.length.side, box.els.backHalf.width.side, box.els.backHalf.length.side);
    box.els.frontHalf.width.side.add(box.els.frontHalf.width.top, box.els.frontHalf.width.bottom);
    box.els.frontHalf.length.side.add(box.els.frontHalf.length.top, box.els.frontHalf.length.bottom);
    box.els.backHalf.width.side.add(box.els.backHalf.width.top, box.els.backHalf.width.bottom);
    box.els.backHalf.length.side.add(box.els.backHalf.length.top, box.els.backHalf.length.bottom);
}

function createBoxElements() {
    for (let halfIdx = 0; halfIdx < 2; halfIdx++) {
        for (let sideIdx = 0; sideIdx < 2; sideIdx++) {

            const half = halfIdx ? 'frontHalf' : 'backHalf';
            const side = sideIdx ? 'width' : 'length';

            const sideWidth = side === 'width' ? box.params.width : box.params.length;
            const flapWidth = sideWidth - 2 * box.params.flapGap;
            const flapHeight = .5 * box.params.width - .75 * box.params.flapGap;

            const sidePlaneGeometry = new THREE.PlaneGeometry(
                sideWidth,
                box.params.depth,
                Math.floor(5 * sideWidth),
                Math.floor(.2 * box.params.depth)
            );
            const flapPlaneGeometry = new THREE.PlaneGeometry(
                flapWidth,
                flapHeight,
                Math.floor(5 * flapWidth),
                Math.floor(.2 * flapHeight)
            );

            const sideGeometry = createSideGeometry(
                sidePlaneGeometry,
                [sideWidth, box.params.depth],
                [true, true, true, true],
                false
            );
            const topGeometry = createSideGeometry(
                flapPlaneGeometry,
                [flapWidth, flapHeight],
                [false, false, true, false],
                true
            );
            const bottomGeometry = createSideGeometry(
                flapPlaneGeometry,
                [flapWidth, flapHeight],
                [true, false, false, false],
                true
            );

            topGeometry.translate(0, .5 * flapHeight, 0);
            bottomGeometry.translate(0, -.5 * flapHeight, 0);

            box.els[half][side].top.geometry = topGeometry;
            box.els[half][side].side.geometry = sideGeometry;
            box.els[half][side].bottom.geometry = bottomGeometry;

            box.els[half][side].top.position.y = .5 * box.params.depth;
            box.els[half][side].bottom.position.y = -.5 * box.params.depth;
        }
    }

    updatePanelsTransform();
}

function createSideGeometry(baseGeometry, size, folds, hasMiddleLayer) {
    const geometriesToMerge = [];
    geometriesToMerge.push(getLayerGeometry(v =>
        -.5 * box.params.thickness + .01 * Math.sin(box.params.fluteFreq * v)
    ));
    geometriesToMerge.push(getLayerGeometry(v =>
        .5 * box.params.thickness + .01 * Math.sin(box.params.fluteFreq * v)
    ));
    if (hasMiddleLayer) {
        geometriesToMerge.push(getLayerGeometry(v =>
            .5 * box.params.thickness * Math.sin(box.params.fluteFreq * v)
        ));
    }

    function getLayerGeometry(offset) {
        const layerGeometry = baseGeometry.clone();
        const positionAttr = layerGeometry.attributes.position;
        for (let i = 0; i < positionAttr.count; i++) {
            const x = positionAttr.getX(i);
            const y = positionAttr.getY(i)
            let z = positionAttr.getZ(i) + offset(x);
            z = applyFolds(x, y, z);
            positionAttr.setXYZ(i, x, y, z);
        }
        return layerGeometry;
    }

    function applyFolds(x, y, z) {
        let modifier = (c, s) => (1. - Math.pow(c / (.5 * s), 10.));
        if ((x > 0 && folds[1]) || (x < 0 && folds[3])) {
            z *= modifier(x, size[0]);
        }
        if ((y > 0 && folds[0]) || (y < 0 && folds[2])) {
            z *= modifier(y, size[1]);
        }
        return z;
    }

    const mergedGeometry = new mergeBufferGeometries(geometriesToMerge, false);
    mergedGeometry.computeVertexNormals();

    return mergedGeometry;
}

// End of box geometries
// --------------------------------------------------

// --------------------------------------------------
// Clickable copyright
// Variables for image manipulation
let isResizing = false;
// let isRotating = false;
let isDragging = false;
let startX, startY;
let initialWidth, initialHeight, initialRotation;
let rotateHandle, deleteButton;

// Function to create the photo upload plane with resize, rotate, and delete controls
function createCopyright() {
    const textInput = document.querySelector('.text-input'); // Match your HTML class

    // Create canvas for the photo texture and text
    const canvas = document.createElement('canvas');
    canvas.width = box.params.copyrightSize[0] * 10;
    canvas.height = box.params.copyrightSize[1] * 10;
    const planeGeometry = new THREE.PlaneGeometry(box.params.copyrightSize[0], box.params.copyrightSize[1]);

    // Get canvas context
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Create texture and plane
    const texture = new THREE.CanvasTexture(canvas);
    copyright = new THREE.Mesh(planeGeometry, new THREE.MeshBasicMaterial({
        map: texture,
        transparent: true,
        opacity: 1
    }));
    scene.add(copyright);

    // Function to update canvas with text
    function updateCanvasText() {
        ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear previous content
        ctx.fillStyle = '#000000'; // Text color
        ctx.font = '50px Helvetica'; // Adjust font size and family as needed
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const text = textInput.value || 'Your Text Here';
        ctx.fillText(text, canvas.width / 2, canvas.height / 2); // Center the text
        texture.needsUpdate = true; // Update the texture
    }

    // Initial text render
    updateCanvasText();

    // Event listener for text input changes
    textInput.addEventListener('input', updateCanvasText);

    // Create a hidden file input for uploading images
    const uploadInput = document.createElement('input');
    uploadInput.type = 'file';
    uploadInput.accept = 'image/*';
    uploadInput.style.display = 'none';
    document.body.appendChild(uploadInput);

    // Event Listener for the Upload Button
    const uploadBtn = document.getElementById('upload-btn');
    uploadBtn.addEventListener('click', () => {
        uploadInput.click();
    });

    // Handle Image Upload
    uploadInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height); // Draw image
                    // Optionally, redraw text over the image
                    ctx.fillStyle = '#000000'; // Text color
                    ctx.font = '50px Helvetica';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    const text = textInput.value || 'Your Text Here';
                    ctx.fillText(text, canvas.width / 2, canvas.height / 2); // Draw text over image
                    texture.needsUpdate = true;
                    createImageControls();

                    // Center the copyright on the front face
                    copyright.position.copy(box.els.frontHalf.length.side.position);
                    copyright.position.x += box.params.length / 2;  // Center of length
                    copyright.position.y = box.els.frontHalf.length.side.position.y;  // Center of depth
                    copyright.position.z += box.params.thickness;  // Just above surface
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Initial positioning
    copyright.position.copy(box.els.frontHalf.length.side.position);
    copyright.position.x += box.params.length / 2;
    copyright.position.y = box.els.frontHalf.length.side.position.y;
    copyright.position.z += box.params.thickness;
}

// Function to create resize, rotate, and delete controls (unchanged)
function createImageControls() {
    // Rotate Handle
    rotateHandle = document.createElement('div');
    rotateHandle.classList.add('rotate-handle');
    document.body.appendChild(rotateHandle);

    // Delete Button
    deleteButton = document.createElement('button');
    deleteButton.classList.add('delete-button');
    deleteButton.innerHTML = 'X';
    document.body.appendChild(deleteButton);

    // Event Listeners for Controls
    rotateHandle.addEventListener('mousedown', startRotate);
    deleteButton.addEventListener('click', deleteImage);

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
}

// Start rotating
function startRotate(event) {
    isRotating = true;
    startX = event.clientX;
    startY = event.clientY;
    initialRotation = copyright.rotation.z;
    document.body.style.cursor = 'grabbing';
}

// End of Clickable copyright
// --------------------------------------------------

// --------------------------------------------------
// Animation

function createFoldingAnimation() {
    // Initial state: box starts closed
    box.animated.openingAngle = 0; // Fully closed
    box.animated.flapAngles.backHalf.width.top = 0;
    box.animated.flapAngles.backHalf.width.bottom = 0;
    box.animated.flapAngles.backHalf.length.top = 0;
    box.animated.flapAngles.backHalf.length.bottom = 0;
    box.animated.flapAngles.frontHalf.width.top = 0;
    box.animated.flapAngles.frontHalf.width.bottom = 0;
    box.animated.flapAngles.frontHalf.length.top = 0;
    box.animated.flapAngles.frontHalf.length.bottom = 0;
    // Update the initial state
    updatePanelsTransform();
    // Timeline for closing the box (from open to closed)
    const closeTimeline = gsap.timeline({
        paused: true, // Start paused, triggered manually
        onUpdate: () => {
            updatePanelsTransform();
        },
        defaults: { ease: 'power1.inOut' }
    });

    closeTimeline
        .to(box.animated.flapAngles.frontHalf.length, {
            duration: 0.9,
            top: 0,
            bottom: 0,
            ease: 'back.out(4)'
        })
        .to(box.animated.flapAngles.backHalf.length, {
            duration: 0.7,
            top: 0,
            bottom: 0,
            ease: 'back.out(3)'
        }, "-=0.7")
        .to([box.animated.flapAngles.backHalf.width, box.animated.flapAngles.frontHalf.width], {
            duration: 0.6,
            top: 0,
            bottom: 0,
            ease: 'back.out(3)'
        }, "-=0.6")
        .to(box.animated, {
            duration: 1,
            openingAngle: 0,
            ease: 'power1.inOut'
        }, "-=0.5");

    // Timeline for opening the box (from closed to open)
    const openTimeline = gsap.timeline({
        paused: true, // Start paused, triggered manually
        onUpdate: () => {
            updatePanelsTransform();
        },
        defaults: { ease: 'power1.inOut' }
    });

    openTimeline
        .to(box.animated, {
            duration: 1,
            openingAngle: 0.5 * Math.PI, // Fully open
            ease: 'power1.inOut'
        })
        .to([box.animated.flapAngles.backHalf.width, box.animated.flapAngles.frontHalf.width], {
            duration: 0.6,
            bottom: 0.6 * Math.PI,
            ease: 'back.in(3)'
        }, "-=0.5") // Overlap with opening for smooth transition
        .to(box.animated.flapAngles.backHalf.length, {
            duration: 0.7,
            bottom: 0.5 * Math.PI,
            ease: 'back.in(2)'
        }, "-=0.4")
        .to(box.animated.flapAngles.frontHalf.length, {
            duration: 0.8,
            bottom: 0.49 * Math.PI,
            ease: 'back.in(3)'
        }, "-=0.6")
        .to([box.animated.flapAngles.backHalf.width, box.animated.flapAngles.frontHalf.width], {
            duration: 0.6,
            top: 0.6 * Math.PI,
            ease: 'back.in(3)'
        }, "-=0.5")
        .to(box.animated.flapAngles.backHalf.length, {
            duration: 0.7,
            top: 0.5 * Math.PI,
            ease: 'back.in(3)'
        }, "-=0.6")
        .to(box.animated.flapAngles.frontHalf.length, {
            duration: 0.9,
            top: 0.49 * Math.PI,
            ease: 'back.in(4)'
        }, "-=0.7");

    // Event listener for "Close Box" button (optional)
    const closeBtn = document.getElementById('close-box-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            openTimeline.pause(); // Stop opening if running
            closeTimeline.restart(); // Start closing animation
        });
    }

    // Event listener for "Open Box" button
    const openBtn = document.getElementById('open-box-btn');
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            closeTimeline.pause(); // Stop closing if running
            openTimeline.restart(); // Start opening animation
        });
    }
}

// No changes needed to updatePanelsTransform unless you want to tweak positioning further
function updatePanelsTransform() {
    // Place width-sides aside of length-sides
    box.els.frontHalf.width.side.position.x = 0.5 * box.params.length;
    box.els.backHalf.width.side.position.x = -0.5 * box.params.length;

    // Rotate width-sides from 0 to 90 deg
    box.els.frontHalf.width.side.rotation.y = box.animated.openingAngle;
    box.els.backHalf.width.side.rotation.y = box.animated.openingAngle;

    // Move length-sides to keep the box centered
    const cos = Math.cos(box.animated.openingAngle);
    box.els.frontHalf.length.side.position.x = -0.5 * cos * box.params.width;
    box.els.backHalf.length.side.position.x = 0.5 * cos * box.params.width;

    // Move length-sides to define box inner space
    const sin = Math.sin(box.animated.openingAngle);
    box.els.frontHalf.length.side.position.z = 0.5 * sin * box.params.width;
    box.els.backHalf.length.side.position.z = -0.5 * sin * box.params.width;

    // Rotate flaps
    box.els.frontHalf.width.top.rotation.x = -box.animated.flapAngles.frontHalf.width.top;
    box.els.frontHalf.length.top.rotation.x = -box.animated.flapAngles.frontHalf.length.top;
    box.els.frontHalf.width.bottom.rotation.x = box.animated.flapAngles.frontHalf.width.bottom;
    box.els.frontHalf.length.bottom.rotation.x = box.animated.flapAngles.frontHalf.length.bottom;

    box.els.backHalf.width.top.rotation.x = box.animated.flapAngles.backHalf.width.top;
    box.els.backHalf.length.top.rotation.x = box.animated.flapAngles.backHalf.length.top;
    box.els.backHalf.width.bottom.rotation.x = -box.animated.flapAngles.backHalf.width.bottom;
    box.els.backHalf.length.bottom.rotation.x = -box.animated.flapAngles.backHalf.length.bottom;

    // Center copyright on front face
    copyright.position.copy(box.els.frontHalf.length.side.position);
    copyright.position.x += box.params.length / 2;
    copyright.position.y = 0; // Center vertically relative to box origin
    copyright.position.z += box.params.thickness + 0.1;
    copyright.rotation.copy(box.els.frontHalf.length.side.rotation);

    // Update textMesh position (if still used)
    if (textMesh) {
        textMesh.position.copy(box.els.frontHalf.length.side.position);
        textMesh.position.x += box.params.length / 2;
        textMesh.position.y = 0;
        textMesh.position.z += box.params.thickness + 0.1;
        textMesh.rotation.copy(box.els.frontHalf.length.side.rotation);
    }
}
// End of animation
// --------------------------------------------------

// --------------------------------------------------
// Manual zoom (buttons only since the scroll is used
// by folding animation)

function createZooming() {
    const zoomInBtn = document.querySelector('#zoom-in');
    const zoomOutBtn = document.querySelector('#zoom-out');

    let zoomLevel = 1;
    const limits = [.4, 2];

    zoomInBtn.addEventListener('click', () => {
        zoomLevel *= 1.3;
        applyZoomLimits();
    });
    zoomOutBtn.addEventListener('click', () => {
        zoomLevel *= .75;
        applyZoomLimits();
    });

    function applyZoomLimits() {
        if (zoomLevel > limits[1]) {
            zoomLevel = limits[1];
            zoomInBtn.classList.add('disabled');
        } else if (zoomLevel < limits[0]) {
            zoomLevel = limits[0];
            zoomOutBtn.classList.add('disabled');
        } else {
            zoomInBtn.classList.remove('disabled');
            zoomOutBtn.classList.remove('disabled');
        }
        gsap.to(camera, {
            duration: .2,
            zoom: zoomLevel,
            onUpdate: () => {
                camera.updateProjectionMatrix();
            }
        });
    }
}

// End of Manual zoom
// --------------------------------------------------

// --------------------------------------------------
// Range sliders for box parameters
function createControls() {
    const gui = new GUI();
    const modalBody = document.querySelector('.content11');

    if (modalBody) {
        modalBody.appendChild(gui.domElement); // Append GUI inside content11
    }

    // Apply styles dynamically based on screen size
    function updateGUIStyles() {
        const screenWidth = window.innerWidth;

        gui.domElement.style.position = 'absolute';
        gui.domElement.style.top = '15px';
        gui.domElement.style.right = '8px';
        gui.domElement.style.zIndex = '1000'; // Ensure it appears above other elements
        gui.domElement.style.width = screenWidth < 768 ? '80%' : '250px'; // Responsive width

        if (screenWidth < 576) {
            // Small screens (mobile)
            gui.domElement.style.left = '50%';
            gui.domElement.style.transform = 'translateX(-50%)';
        } else {
            // Tablets and desktops
            gui.domElement.style.left = 'auto';
            gui.domElement.style.transform = 'none';
        }
    }

    // Initial call and event listener for resizing
    updateGUIStyles();
    window.addEventListener("resize", updateGUIStyles);

    // Add GUI controls
    gui.add(box.params, 'width', box.params.widthLimits[0], box.params.widthLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
            updatePanelsTransform();
        });

    gui.add(box.params, 'length', box.params.lengthLimits[0], box.params.lengthLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
            updatePanelsTransform();
        });

    gui.add(box.params, 'depth', box.params.depthLimits[0], box.params.depthLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
            updatePanelsTransform();
        });

    gui.add(box.params, 'fluteFreq', box.params.fluteFreqLimits[0], box.params.fluteFreqLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
        }).name('Flute');

    gui.add(box.params, 'thickness', box.params.thicknessLimits[0], box.params.thicknessLimits[1])
        .step(0.05).onChange(() => {
            createBoxElements();
        });
}

// End Range sliders for box parameters
// --------------------------------------------------

// Power start 😁
// 3D Box Controller
let selectedFace = null;
function setupFaceViewControls() {
    const btnFront = document.querySelector('.b1');
    const btnLeft = document.querySelector('.b2');
    const btnBack = document.querySelector('.b3');
    const btnRight = document.querySelector('.b4');
    const btnTop = document.querySelector('.b5');
    const btnBottom = document.querySelector('.b6');
    const distance = 170;

    // Animate camera movement and orientation
    function moveCamera(position, lookAtTarget) {
        gsap.to(camera.position, {
            x: position.x,
            y: position.y,
            z: position.z,
            duration: 1,
            onUpdate: () => {
                camera.lookAt(lookAtTarget);
                camera.updateProjectionMatrix();
            }
        });
    }

    // Event Listeners for each button
    btnFront.addEventListener('click', () => {selectedFace = 'front'; moveCamera({ x: -80, y: 60, z: distance }, box.position);});
    btnBack.addEventListener('click', () => {selectedFace = 'back'; moveCamera({ x: 0, y: 0, z: -distance }, box.position);});
    btnLeft.addEventListener('click', () => {selectedFace = 'left'; moveCamera({ x: -distance, y: 0, z: 0 }, box.position);});
    btnRight.addEventListener('click', () => {selectedFace = 'right'; moveCamera({ x: distance, y: 0, z: 0 }, box.position);});
    btnTop.addEventListener('click', () => {selectedFace = 'top'; moveCamera({ x: 0, y: distance, z: 0 }, box.position);});
    btnBottom.addEventListener('click', () => {selectedFace = 'bottom'; moveCamera({ x: 0, y: -distance, z: 0 }, box.position);});

}

// For Rotate button
let isRotating = false;
let rotationRequest; // Store the animation frame request

// Event Listener for Rotate Button
document.getElementById('rotate-btn').addEventListener('click', () => {
    isRotating = !isRotating; // Toggle rotation state using '!'
    
    if (isRotating) {
        animateRotation(); // Start rotation
    }
});

// Function to Animate Rotation of the 3D Box
function animateRotation() {
    if (!isRotating) return; // Stop rotation if the state is false
    if (box && box.els && box.els.group) {
        // Rotate the box slightly to the right on the Y-axis
        box.els.group.rotation.y += 0.25 * 0.01;
    } else {
        console.warn('Box group not found in the scene.');
    }
    // Update the scene
    orbit.update();
    renderer.render(scene, camera);
    // Continuously animate while rotating is true
    rotationRequest = requestAnimationFrame(animateRotation);
}