<?php
// Usage: define $breadcrumbs = [ ['label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'fa-home'], ... ];
if (!isset($breadcrumbs) || !is_array($breadcrumbs) || count($breadcrumbs) === 0) return;
?>
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <li class="inline-flex items-center">
                <?php if ($i > 0): ?>
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                <?php endif; ?>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?php echo htmlspecialchars($crumb['url']); ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        <?php if (!empty($crumb['icon'])): ?>
                            <i class="fas <?php echo htmlspecialchars($crumb['icon']); ?> mr-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($crumb['label']); ?>
                    </a>
                <?php else: ?>
                    <span class="text-sm font-medium text-gray-500 inline-flex items-center">
                        <?php if (!empty($crumb['icon'])): ?>
                            <i class="fas <?php echo htmlspecialchars($crumb['icon']); ?> mr-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($crumb['label']); ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav> 