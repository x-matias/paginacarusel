document.addEventListener('DOMContentLoaded', () => {
    // --- Lógica del Carrusel ---
    const track = document.getElementById('carouselTrack');
    if (track) {
        const slides = Array.from(track.children);
        const nextButton = document.getElementById('nextBtn');
        const prevButton = document.getElementById('prevBtn');
        const navIndicators = document.querySelectorAll('.carousel-indicator');
        
        let currentIndex = 0;

        const updateCarousel = (newIndex) => {
            // Remover estado activo
            slides[currentIndex].classList.remove('current-slide');
            navIndicators[currentIndex].classList.remove('current-indicator');

            // Actualizar index
            currentIndex = newIndex;

            // En caso de que se pase de los límites (loop)
            if (currentIndex >= slides.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = slides.length - 1;

            // Setear nuevo estado activo
            slides[currentIndex].classList.add('current-slide');
            navIndicators[currentIndex].classList.add('current-indicator');
        };

        // Eventos Botones
        nextButton.addEventListener('click', () => {
            updateCarousel(currentIndex + 1);
        });

        prevButton.addEventListener('click', () => {
            updateCarousel(currentIndex - 1);
        });

        // Eventos Indicadores
        navIndicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                updateCarousel(index);
            });
        });

        // Auto-play opcional
        setInterval(() => {
            updateCarousel(currentIndex + 1);
        }, 5000);
    }

    // --- Lógica de Previsualización y Drag & Drop ---
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('photoInput');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const fileMsg = document.querySelector('.file-msg');

    if(dropArea && fileInput) {
        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('is-active'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('is-active'), false);
        });

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files; // Asignar archivo al input real
                updatePreview(files[0]);
            }
        }

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                updatePreview(this.files[0]);
            }
        });

        function updatePreview(file) {
            if (file.type.startsWith('image/')) {
                fileMsg.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                fileMsg.textContent = "Por favor selecciona una imagen válida.";
                previewContainer.classList.add('hidden');
            }
        }
    }
});
