
<section class="relative h-[90vh] min-h-[600px] flex items-center justify-center overflow-hidden bg-gray-900"
         x-data="{
             current: 0,
             total: <?php echo e($heroSlides->count() ?: 1); ?>,
             autoplay: null,
             transitioning: false,
             init() {
                 if (this.total > 1) {
                     this.startAutoplay();
                 }
             },
             startAutoplay() {
                 this.autoplay = setInterval(() => this.next(), 6000);
             },
             stopAutoplay() {
                 clearInterval(this.autoplay);
             },
             next() {
                 if (this.transitioning) return;
                 this.transitioning = true;
                 this.current = (this.current + 1) % this.total;
                 setTimeout(() => this.transitioning = false, 1000);
             },
             prev() {
                 if (this.transitioning) return;
                 this.transitioning = true;
                 this.current = (this.current - 1 + this.total) % this.total;
                 setTimeout(() => this.transitioning = false, 1000);
             },
             goTo(index) {
                 if (this.transitioning || index === this.current) return;
                 this.stopAutoplay();
                 this.transitioning = true;
                 this.current = index;
                 setTimeout(() => this.transitioning = false, 1000);
                 this.startAutoplay();
             }
         }"
         @mouseenter="stopAutoplay()"
         @mouseleave="startAutoplay()">

    <?php if($heroSlides->count()): ?>
        
        <?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="absolute inset-0 z-0"
                 x-show="current === <?php echo e($index); ?>"
                 x-transition:enter="transition-opacity duration-1000 ease-in-out"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-1000 ease-in-out"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                
                <div class="absolute inset-0 animate-ken-burns">
                    <img src="<?php echo e(asset('storage/' . $slide->image)); ?>"
                         alt="<?php echo e($slide->title); ?>"
                         class="w-full h-full object-cover">
                </div>

                
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-black/30"></div>
                <div class="absolute inset-0 bg-gray-900/20 backdrop-brightness-75"></div>

                
                <div class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-gray-900/60 to-transparent"></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="container mx-auto px-4 relative z-10 text-center"
                 x-show="current === <?php echo e($index); ?>"
                 x-transition:enter="transition-all duration-700 delay-300 ease-out"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition-all duration-300 ease-in"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4">

                <div class="max-w-5xl mx-auto space-y-8">

                    
                    <?php if($slide->subtitle): ?>
                        <div class="inline-block mb-4">
                            <span class="hero-tagline px-5 py-2 rounded-full border border-white/20 text-white/90 text-sm font-medium tracking-[0.2em] uppercase backdrop-blur-md bg-white/5">
                                <?php echo e($slide->subtitle); ?>

                            </span>
                        </div>
                    <?php endif; ?>

                    
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-serif text-white leading-[0.95] tracking-tight drop-shadow-2xl">
                        <?php echo nl2br(e($slide->title)); ?>

                    </h1>

                    
                    <?php if($slide->description): ?>
                        <p class="text-xl md:text-2xl text-gray-200 font-light max-w-2xl mx-auto leading-relaxed opacity-90">
                            <?php echo e($slide->description); ?>

                        </p>
                    <?php endif; ?>

                    
                    <div class="pt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <?php if($slide->button_text): ?>
                            <a href="<?php echo e($slide->button_url); ?>"
                               class="hero-btn-primary min-w-[200px] px-8 py-4 bg-white text-gray-900 font-medium rounded-full hover:bg-rose-50 transition-all duration-300 transform hover:-translate-y-1 shadow-[0_0_30px_rgba(255,255,255,0.25)] hover:shadow-[0_0_50px_rgba(255,255,255,0.4)]">
                                <?php echo e($slide->button_text); ?>

                            </a>
                        <?php endif; ?>
                        <?php if($slide->secondary_button_text): ?>
                            <a href="<?php echo e($slide->secondary_button_url); ?>"
                               class="hero-btn-secondary min-w-[200px] px-8 py-4 bg-transparent border border-white/30 text-white font-medium rounded-full hover:bg-white/10 hover:border-white transition-all duration-300 backdrop-blur-sm">
                                <?php echo e($slide->secondary_button_text); ?>

                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($heroSlides->count() > 1): ?>
            <button @click="stopAutoplay(); prev(); startAutoplay()"
                    class="absolute left-6 top-1/2 -translate-y-1/2 z-20 w-14 h-14 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center hover:bg-white/25 hover:scale-110 transition-all duration-300 shadow-2xl hidden md:flex group">
                <i class="ph ph-caret-left text-xl group-hover:-translate-x-0.5 transition-transform"></i>
            </button>
            <button @click="stopAutoplay(); next(); startAutoplay()"
                    class="absolute right-6 top-1/2 -translate-y-1/2 z-20 w-14 h-14 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white flex items-center justify-center hover:bg-white/25 hover:scale-110 transition-all duration-300 shadow-2xl hidden md:flex group">
                <i class="ph ph-caret-right text-xl group-hover:translate-x-0.5 transition-transform"></i>
            </button>

            
            <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3">
                <?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button @click="goTo(<?php echo e($index); ?>)"
                            class="group relative overflow-hidden rounded-full transition-all duration-500"
                            :class="current === <?php echo e($index); ?>

                                ? 'w-10 h-2.5 bg-white shadow-[0_0_15px_rgba(255,255,255,0.5)]'
                                : 'w-2.5 h-2.5 bg-white/40 hover:bg-white/70'">
                        <span class="absolute inset-0 bg-rose-400 rounded-full origin-left transition-transform duration-[6000ms] ease-linear"
                              :class="current === <?php echo e($index); ?> ? 'scale-x-100' : 'scale-x-0'"
                              x-show="current === <?php echo e($index); ?>"></span>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        
        <div class="absolute inset-0 z-0">
            <img src="<?php echo e(asset('storage/banner.jpg')); ?>" alt="Hero Background" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-black/30"></div>
            <div class="absolute inset-0 bg-gray-900/20 backdrop-brightness-75"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <div class="max-w-5xl mx-auto space-y-8 animate-fade-in-up">
                <div class="inline-block mb-4">
                    <span class="px-4 py-1.5 rounded-full border border-white/30 text-white/90 text-sm font-medium tracking-widest uppercase backdrop-blur-sm">
                        Kigali's Premier Nail Salon
                    </span>
                </div>
                <h1 class="text-5xl md:text-7xl lg:text-8xl font-serif text-white leading-tight tracking-tight drop-shadow-lg">
                    Artistry at Your <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-white">Fingertips</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-200 font-light max-w-2xl mx-auto leading-relaxed opacity-90">
                    Experience the perfect blend of luxury, hygiene, and style.
                </p>
                <div class="pt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="<?php echo e(route('booking.step1')); ?>" class="min-w-[200px] px-8 py-4 bg-white text-gray-900 font-medium rounded-full hover:bg-rose-50 transition-all duration-300 transform hover:-translate-y-1 shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                        Book Appointment
                    </a>
                    <a href="<?php echo e(url('/services')); ?>" class="min-w-[200px] px-8 py-4 bg-transparent border border-white/30 text-white font-medium rounded-full hover:bg-white/10 hover:border-white transition-all duration-300 backdrop-blur-sm">
                        View Services
                    </a>
                </div>
            </div>
        </div>

        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-10 animate-bounce">
            <span class="text-white/50 text-sm uppercase tracking-widest mb-2 block text-center text-[10px]">Scroll</span>
            <i class="ph ph-caret-down text-2xl text-white/70"></i>
        </div>
    <?php endif; ?>

    
    <?php if($heroSlides->count()): ?>
        <div class="absolute bottom-10 right-8 z-20 hidden md:flex flex-col items-center gap-2 animate-bounce">
            <span class="text-white/40 text-[10px] uppercase tracking-widest writing-vertical">Scroll</span>
            <div class="w-px h-8 bg-gradient-to-b from-white/40 to-transparent"></div>
        </div>
    <?php endif; ?>
</section>

<style>
    /* Ken Burns zoom effect */
    @keyframes kenBurns {
        0% { transform: scale(1); }
        100% { transform: scale(1.08); }
    }
    .animate-ken-burns {
        animation: kenBurns 10s ease-out forwards;
    }

    /* Hero entrance animations */
    .hero-tagline {
        animation: heroTaglineFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) 0.2s both;
    }

    @keyframes heroTaglineFade {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 40px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    .animate-fade-in-up {
        animation: fadeInUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    /* Vertical writing for scroll indicator */
    .writing-vertical {
        writing-mode: vertical-rl;
        text-orientation: mixed;
    }

    /* Smooth btn hover glow */
    .hero-btn-primary {
        position: relative;
        overflow: hidden;
    }
    .hero-btn-primary::after {
        content: '';
        position: absolute;
        inset: -2px;
        background: linear-gradient(45deg, rgba(244,63,94,0.3), rgba(255,255,255,0.3), rgba(244,63,94,0.3));
        border-radius: 9999px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: -1;
    }
    .hero-btn-primary:hover::after {
        opacity: 1;
    }
</style><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views/partials/hero.blade.php ENDPATH**/ ?>