<?php
/**
 * Modal Component
 * Usage: include with $modal array containing id, title, content, etc.
 */

$modalId = $modal['id'] ?? 'default-modal';
$modalTitle = $modal['title'] ?? 'Modal';
$modalSize = $modal['size'] ?? 'md'; // sm, md, lg, xl
$showCloseButton = $modal['show_close'] ?? true;
$backdrop = $modal['backdrop'] ?? true;

$sizeClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '4xl' => 'max-w-4xl'
];

$modalClass = $sizeClasses[$modalSize] ?? $sizeClasses['md'];
?>

<div id="<?php echo $modalId; ?>" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" 
     <?php if ($backdrop): ?>onclick="closeModal('<?php echo $modalId; ?>', event)"<?php endif; ?>>
    <div class="relative top-20 mx-auto p-5 border w-full <?php echo $modalClass; ?> shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                <?php if (isset($modal['icon'])): ?>
                    <i class="<?php echo htmlspecialchars($modal['icon']); ?> mr-2"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($modalTitle); ?>
            </h3>
            
            <?php if ($showCloseButton): ?>
                <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" 
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Modal Content -->
        <div class="modal-content">
            <?php if (isset($modal['content'])): ?>
                <?php echo $modal['content']; ?>
            <?php endif; ?>
        </div>
        
        <!-- Modal Footer -->
        <?php if (isset($modal['footer'])): ?>
            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                <?php echo $modal['footer']; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus first input if available
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeModal(modalId, event) {
    // If event is provided, check if click was on backdrop
    if (event && event.target !== event.currentTarget) {
        return;
    }
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Clear any form data if needed
        const form = modal.querySelector('form');
        if (form && form.dataset.clearOnClose !== 'false') {
            form.reset();
        }
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
        visibleModals.forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// Prevent body scroll when modal is open
function toggleBodyScroll(disable) {
    if (disable) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}
</script>