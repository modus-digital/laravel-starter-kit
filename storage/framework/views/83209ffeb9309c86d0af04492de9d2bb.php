<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildSchema()); ?>

</div>
<?php /**PATH E:\projects\starter-kits\modus-starter-kit-v2\vendor\filament\schemas\resources\views/components/grid.blade.php ENDPATH**/ ?>