

<?php $__env->startSection('title', 'FAQ | Nail Salon Booking, Payments & Policies - Isaiah Nail Bar Kigali'); ?>

<?php $__env->startSection('meta_description', 'Frequently asked questions about Isaiah Nail Bar in Kigali, Rwanda. Learn about booking deposits, payment methods (MTN MoMo, card), cancellation policy, opening hours & more.'); ?>
<?php $__env->startSection('meta_keywords', 'Isaiah Nail Bar FAQ, nail salon booking Kigali, nail salon deposit Rwanda, MTN MoMo nail salon, nail appointment Kigali, cancel nail appointment Rwanda, nail salon payment methods Rwanda'); ?>

<?php $__env->startPush('schema'); ?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Is a deposit required to book an appointment at Isaiah Nail Bar?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, a non-refundable deposit of 40% is required to secure your booking. This amount will be deducted from your final payment."
      }
    },
    {
      "@type": "Question",
      "name": "What are the available payment methods at Isaiah Nail Bar?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "We accept payments via MTN Mobile Money, Cash, or Bank Transfer depending on service type. Payment instructions will be provided after confirming the booking."
      }
    },
    {
      "@type": "Question",
      "name": "What happens if I'm late to my nail appointment?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "If you are more than 15 minutes late, your appointment may be canceled and your deposit forfeited. Please arrive on time to avoid disruptions."
      }
    },
    {
      "@type": "Question",
      "name": "Can I cancel or reschedule my nail appointment?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, but cancellations must be made at least 48 hours before the appointment. Otherwise, the deposit will be forfeited. Rescheduling is allowed within the same notice period."
      }
    },
    {
      "@type": "Question",
      "name": "Can I bring someone with me to my appointment at Isaiah Nail Bar?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, you may bring one guest. However, they must remain quiet and respectful to maintain a relaxing atmosphere for all clients."
      }
    }
  ]
}
</script>


<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home", "item": "<?php echo e(url('/')); ?>"},
    {"@type": "ListItem", "position": 2, "name": "FAQ", "item": "<?php echo e(url('/faq')); ?>"}
  ]
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="relative bg-gray-900 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-30">
        <img src="<?php echo e(asset('storage/banner.jpg')); ?>" alt="Isaiah Nail Bar FAQ - Frequently Asked Questions" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center pt-12">
        <span class="text-rose-400 font-medium tracking-widest text-sm uppercase mb-3 block">Need Answers?</span>
        <h1 class="text-4xl md:text-6xl font-serif text-white mb-6">Frequently Asked Questions</h1>
        <p class="text-xl text-gray-300 font-light max-w-2xl mx-auto">
            Find answers to common questions about booking, payments, and policies at Isaiah Nail Bar Kigali.
        </p>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 mb-8 text-center">
            <p class="text-lg font-bold text-yellow-900">
                🎉 <span class="text-yellow-800">First-Time Clients</span> get <span class="text-yellow-800">10% OFF</span> your first appointment!
            </p>
        </div>

        
        <div class="space-y-4">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq1')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-calendar-check text-blue-600"></i>
                        Is a deposit required to book an appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq1"></i>
                </button>
                <div id="faq1" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, a <strong>non-refundable deposit</strong> of <strong>40%</strong> is required to secure your booking. This amount will be deducted from your final payment.</p>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq2')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-credit-card text-blue-600"></i>
                        What are the available payment methods?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq2"></i>
                </button>
                <div id="faq2" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">We accept payments via <strong>MTN Mobile Money</strong>, <strong>Cash</strong>, or <strong>Bank Transfer</strong> depending on service type. Payment instructions will be provided after confirming the booking.</p>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq3')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-clock-afternoon text-blue-600"></i>
                        What happens if I'm late to my appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq3"></i>
                </button>
                <div id="faq3" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">If you are more than <strong>15 minutes late</strong>, your appointment may be canceled and your deposit forfeited. Please arrive on time to avoid disruptions.</p>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq4')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-x-circle text-blue-600"></i>
                        Can I cancel or reschedule my booking?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq4"></i>
                </button>
                <div id="faq4" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, but cancellations must be made <strong>at least 48 hours</strong> before the appointment. Otherwise, the deposit will be forfeited. Rescheduling is allowed within the same notice period.</p>
                </div>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <button type="button" onclick="toggleFaq('faq5')" class="w-full px-6 py-4 text-left font-semibold text-gray-900 hover:bg-gray-50 transition flex items-center justify-between">
                    <span class="flex items-center gap-3">
                        <i class="ph ph-users-three text-blue-600"></i>
                        Can I bring someone with me to my appointment?
                    </span>
                    <i class="ph ph-caret-down transition-transform" id="icon-faq5"></i>
                </button>
                <div id="faq5" class="hidden px-6 py-4 border-t bg-gray-50">
                    <p class="text-gray-700">Yes, you may bring one guest. However, they must remain quiet and respectful to maintain a relaxing atmosphere for all clients.</p>
                </div>
            </div>
        </div>

        
        <div class="text-center mt-12">
            <p class="text-gray-600 mb-6">Still have questions?</p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="<?php echo e(url('/contact')); ?>" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Contact Us
                </a>
                <a href="<?php echo e(route('booking.step1')); ?>" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                    Book Now
                </a>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleFaq(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mouba\isaiahnailbar\resources\views\faq.blade.php ENDPATH**/ ?>