<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    
    <url>
        <loc><?php echo e(url('/')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.00</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/services')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/booking')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.90</priority>
    </url>

    
    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(url('/services/' . $service->id)); ?></loc>
        <lastmod><?php echo e($service->updated_at->toAtomString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.80</priority>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <url>
        <loc><?php echo e(url('/about')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/gallery')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.70</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/contact')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/faq')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.70</priority>
    </url>

    
    <url>
        <loc><?php echo e(url('/providers')); ?></loc>
        <lastmod><?php echo e(now()->toAtomString()); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.60</priority>
    </url>

</urlset>
<?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\sitemap.blade.php ENDPATH**/ ?>