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
        breadth: 27,
        breadthLimits: [15, 70],
        length: 80,
        lengthLimits: [40, 120],
        height: 45,
        heightLimits: [15, 70],
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
            breadth: {
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
            breadth: {
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
                breadth: {
                    top: 0,
                    bottom: 0
                },
                length: {
                    top: 0,
                    bottom: 0
                },
            },
            frontHalf: {
                breadth: {
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
    box.els.group.add(box.els.frontHalf.breadth.side, box.els.frontHalf.length.side, box.els.backHalf.breadth.side, box.els.backHalf.length.side);
    box.els.frontHalf.breadth.side.add(box.els.frontHalf.breadth.top, box.els.frontHalf.breadth.bottom);
    box.els.frontHalf.length.side.add(box.els.frontHalf.length.top, box.els.frontHalf.length.bottom);
    box.els.backHalf.breadth.side.add(box.els.backHalf.breadth.top, box.els.backHalf.breadth.bottom);
    box.els.backHalf.length.side.add(box.els.backHalf.length.top, box.els.backHalf.length.bottom);
}

function createBoxElements() {
    for (let halfIdx = 0; halfIdx < 2; halfIdx++) {
        for (let sideIdx = 0; sideIdx < 2; sideIdx++) {

            const half = halfIdx ? 'frontHalf' : 'backHalf';
            const side = sideIdx ? 'breadth' : 'length';

            const sideBreadth = side === 'breadth' ? box.params.breadth : box.params.length;
            const flapBreadth = sideBreadth - 2 * box.params.flapGap;
            const flapHeight = .5 * box.params.breadth - .75 * box.params.flapGap;

            const sidePlaneGeometry = new THREE.PlaneGeometry(
                sideBreadth,
                box.params.height,
                Math.floor(5 * sideBreadth),
                Math.floor(.2 * box.params.height)
            );
            const flapPlaneGeometry = new THREE.PlaneGeometry(
                flapBreadth,
                flapHeight,
                Math.floor(5 * flapBreadth),
                Math.floor(.2 * flapHeight)
            );

            const sideGeometry = createSideGeometry(
                sidePlaneGeometry,
                [sideBreadth, box.params.height],
                [true, true, true, true],
                false
            );
            const topGeometry = createSideGeometry(
                flapPlaneGeometry,
                [flapBreadth, flapHeight],
                [false, false, true, false],
                true
            );
            const bottomGeometry = createSideGeometry(
                flapPlaneGeometry,
                [flapBreadth, flapHeight],
                [true, false, false, false],
                true
            );

            topGeometry.translate(0, .5 * flapHeight, 0);
            bottomGeometry.translate(0, -.5 * flapHeight, 0);

            box.els[half][side].top.geometry = topGeometry;
            box.els[half][side].side.geometry = sideGeometry;
            box.els[half][side].bottom.geometry = bottomGeometry;

            box.els[half][side].top.position.y = .5 * box.params.height;
            box.els[half][side].bottom.position.y = -.5 * box.params.height;
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
            const y = positionAttr.getY(i);
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

function createCopyright() {
    const textInput = document.querySelector('.text-input');
    if (!textInput) {
        console.error('Text input element with class "text-input" not found in the DOM.');
        return;
    }
    // Dynamic size based on box dimensions, constrained to fit the front face
    const maxBreadth = box.params.breadth;
    const maxHeight = box.params.height;
    const copyrightBreadth = Math.min(box.params.breadth * 0.8, maxBreadth * 0.9); // Use breadth for front face
    const copyrightHeight = Math.min(box.params.height * 0.5, maxHeight * 0.9);
    box.params.copyrightSize = [copyrightBreadth, copyrightHeight];

    // Create canvas for the photo texture and text
    const canvas = document.createElement('canvas');
    canvas.width = copyrightBreadth * 20;
    canvas.height = copyrightHeight * 20;
    const planeGeometry = new THREE.PlaneGeometry(copyrightBreadth, copyrightHeight);
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Failed to get 2D context for canvas.');
        return;
    }
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Create texture and plane
    const texture = new THREE.CanvasTexture(canvas);
    copyright = new THREE.Mesh(planeGeometry, new THREE.MeshBasicMaterial({
        map: texture,
        transparent: true,
        opacity: 1
    }));
    scene.add(copyright);

    // Function to update canvas with text and scale font
    function updateCanvasText() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#000000';
        const fontSize = Math.min(copyrightHeight * 20, 50); // Adjusted for better scaling
        ctx.font = `${fontSize}px Helvetica`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const text = textInput.value || 'Your Text Here';
        ctx.fillText(text, canvas.width / 2, canvas.height / 2);
        texture.needsUpdate = true;
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
    if (!uploadBtn) {
        console.error('Upload button with ID "upload-btn" not found in the DOM.');
    } else {
        uploadBtn.addEventListener('click', () => {
            uploadInput.click();
        });
    }

    // Handle Image Upload
    uploadInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) {
            console.warn('No file selected for upload.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const aspectRatio = img.width / img.height;
                let drawWidth = canvas.width;
                let drawHeight = canvas.height;
                if (drawWidth / drawHeight > aspectRatio) {
                    drawWidth = drawHeight * aspectRatio;
                } else {
                    drawHeight = drawWidth / aspectRatio;
                }
                const xOffset = (canvas.width - drawWidth) / 2;
                const yOffset = (canvas.height - drawHeight) / 2;
                ctx.drawImage(img, xOffset, yOffset, drawWidth, drawHeight);
                ctx.fillStyle = '#000000';
                const fontSize = Math.min(copyrightHeight * 20, 50);
                ctx.font = `${fontSize}px Helvetica`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const text = textInput.value || 'Your Text Here';
                ctx.fillText(text, canvas.width / 2, canvas.height / 2);
                texture.needsUpdate = true;
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
    // Initial positioning
    const frontLengthSide = box.els.frontHalf.length.side;
    copyright.position.copy(frontLengthSide.position);
    copyright.position.x += box.params.breadth / 2 - (copyright.geometry.parameters.width / 2);
    copyright.position.y = 0;
    copyright.position.z = frontLengthSide.position.z + (box.params.thickness / 2);
    copyright.rotation.copy(frontLengthSide.rotation);
}

// End of Clickable copyright
// --------------------------------------------------

// --------------------------------------------------
// Animation

function createFoldingAnimation() {
    // Initial state: box starts closed
    box.animated.openingAngle = 0; // Fully closed
    box.animated.flapAngles.backHalf.breadth.top = 0;
    box.animated.flapAngles.backHalf.breadth.bottom = 0;
    box.animated.flapAngles.backHalf.length.top = 0;
    box.animated.flapAngles.backHalf.length.bottom = 0;
    box.animated.flapAngles.frontHalf.breadth.top = 0;
    box.animated.flapAngles.frontHalf.breadth.bottom = 0;
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
        .to([box.animated.flapAngles.backHalf.breadth, box.animated.flapAngles.frontHalf.breadth], {
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
        .to([box.animated.flapAngles.backHalf.breadth, box.animated.flapAngles.frontHalf.breadth], {
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
        .to([box.animated.flapAngles.backHalf.breadth, box.animated.flapAngles.frontHalf.breadth], {
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
    const closeBtn = document.getElementById('close-btn-box');
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
    // Place breadth-sides aside of length-sides
    box.els.frontHalf.breadth.side.position.x = 0.5 * box.params.length;
    box.els.backHalf.breadth.side.position.x = -0.5 * box.params.length;

    // Rotate breadth-sides from 0 to 90 deg
    box.els.frontHalf.breadth.side.rotation.y = box.animated.openingAngle;
    box.els.backHalf.breadth.side.rotation.y = box.animated.openingAngle;

    // Move length-sides to keep the box centered
    const cos = Math.cos(box.animated.openingAngle);
    box.els.frontHalf.length.side.position.x = -0.5 * cos * box.params.breadth;
    box.els.backHalf.length.side.position.x = 0.5 * cos * box.params.breadth;

    // Move length-sides to define box inner space
    const sin = Math.sin(box.animated.openingAngle);
    box.els.frontHalf.length.side.position.z = 0.5 * sin * box.params.breadth;
    box.els.backHalf.length.side.position.z = -0.5 * sin * box.params.breadth;

    // Rotate flaps
    box.els.frontHalf.breadth.top.rotation.x = -box.animated.flapAngles.frontHalf.breadth.top;
    box.els.frontHalf.length.top.rotation.x = -box.animated.flapAngles.frontHalf.length.top;
    box.els.frontHalf.breadth.bottom.rotation.x = box.animated.flapAngles.frontHalf.breadth.bottom;
    box.els.frontHalf.length.bottom.rotation.x = box.animated.flapAngles.frontHalf.length.bottom;

    box.els.backHalf.breadth.top.rotation.x = box.animated.flapAngles.backHalf.breadth.top;
    box.els.backHalf.length.top.rotation.x = box.animated.flapAngles.backHalf.length.top;
    box.els.backHalf.breadth.bottom.rotation.x = -box.animated.flapAngles.backHalf.breadth.bottom;
    box.els.backHalf.length.bottom.rotation.x = -box.animated.flapAngles.backHalf.length.bottom;

    const frontLengthSide = box.els.frontHalf.length.side;
    copyright.position.copy(frontLengthSide.position);
    // Center the copyright on the front face
    copyright.position.x += box.params.length / 2 - (copyright.geometry.parameters.width / 2); // Offset to center horizontally
    copyright.position.y = 0; // Center vertically
    copyright.position.z = frontLengthSide.position.z + (box.params.thickness / 2) + 0.1; // Slightly in front of the front face
    copyright.rotation.copy(frontLengthSide.rotation);

    // Update textMesh position (if still used)
    if (textMesh) {
        textMesh.position.copy(box.els.frontHalf.length.side.position);
        textMesh.position.x += box.params.length / 2 - 10;
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

    zoomInBtn.addEventListener('click', () => { zoomLevel *= 1.3; applyZoomLimits(); });
    zoomOutBtn.addEventListener('click', () => { zoomLevel *= .75; applyZoomLimits(); });

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
    const modalBody = document.querySelector('.modal-panel-left');

    if (modalBody) {
        modalBody.appendChild(gui.domElement); // Append GUI inside content11
    }

    // Apply styles dynamically based on screen size
    function updateGUIStyles() {
        const screenWidth = window.innerWidth;

        gui.domElement.style.position = 'absolute';
        gui.domElement.style.top = '15px';
        gui.domElement.style.left = '8px';
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
    gui.add(box.params, 'breadth', box.params.breadthLimits[0], box.params.breadthLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
            updatePanelsTransform();
        });

    gui.add(box.params, 'length', box.params.lengthLimits[0], box.params.lengthLimits[1])
        .step(1).onChange(() => {
            createBoxElements();
            updatePanelsTransform();
        });

    gui.add(box.params, 'height', box.params.heightLimits[0], box.params.heightLimits[1])
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
// let isRotating = false;
// let rotationRequest; // Store the animation frame request

// Event Listener for Rotate Button
// document.getElementById('rotate-btn').addEventListener('click', () => {
//     isRotating = !isRotating; // Toggle rotation state
//     if (isRotating) {
//         animateRotation(); // Start rotation
//     } else {
//         cancelAnimationFrame(rotationRequest); // Stop rotation when toggled off
//     }
// });

// Function to Animate Rotation of the 3D Box
// function animateRotation() {
//     if (!isRotating) return; // Stop rotation if the state is false
//     if (box && box.els && box.els.group) {
//         box.els.group.rotation.y += 0.25 * 0.01;
//         copyright.rotation.y = box.els.group.rotation.y;
//     } else {
//         console.warn('Box group not found in the scene.');
//     }
//     orbit.update();
//     renderer.render(scene, camera);
//     rotationRequest = requestAnimationFrame(animateRotation);
// }

// Event Listener for Reset Button
// document.getElementById('reset-btn').addEventListener('click', () => {
//     console.log("Reset button is pressed");
//     if (ctx) {
//         ctx.clearRect(0, 0, canvas.width, canvas.height);
//         const textInput = document.querySelector('.text-input');
//         if (textInput) {
//             textInput.value = '';
//         }
//         copyright.material.map = new THREE.CanvasTexture(canvas); // Recreate texture
//         updateCanvasText();
//         updateCopyrightPosition();
//     } else {
//         console.error('Canvas context is not available.');
//     }
// });
// // Helper function to update copyright position
// function updateCopyrightPosition() {
//     const frontLengthSide = box.els.frontHalf.length.side;
//     copyright.position.copy(frontLengthSide.position);
//     copyright.position.x += box.params.breadth / 2 - (copyright.geometry.parameters.width / 2);
//     copyright.position.y = 0;
//     copyright.position.z = frontLengthSide.position.z + (box.params.thickness / 2); // No extra offset
//     copyright.rotation.copy(frontLengthSide.rotation);
// }