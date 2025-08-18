<?php
// Breadcrumb component for admin panel
// Usage: include this file and call renderBreadcrumb($breadcrumbs) function

function renderBreadcrumb($breadcrumbs) {
    if (!isset($breadcrumbs) || empty($breadcrumbs)) {
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => 'index.php', 'active' => false]
        ];
    }
    
    // Ensure all breadcrumb items have required keys
    foreach ($breadcrumbs as &$breadcrumb) {
        if (!isset($breadcrumb['active'])) {
            $breadcrumb['active'] = false;
        }
        if (!isset($breadcrumb['url'])) {
            $breadcrumb['url'] = '';
        }
    }
    
    // Mark the last item as active
    if (!empty($breadcrumbs)) {
        $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
    }
    ?>
    
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <li class="inline-flex items-center">
                    <?php if ($index > 0): ?>
                        <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                    
                    <?php if (isset($breadcrumb['active']) && $breadcrumb['active']): ?>
                        <span class="text-sm font-medium text-gray-500">
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" 
                           class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                            <?php if ($index === 0): ?>
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
}
?> 