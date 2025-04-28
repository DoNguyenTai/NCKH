<?php $__currentLoopData = $typeOfForm->fieldForm; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <input type="<?php echo e($item->data_type); ?>" placeholder="<?php echo e($item->value); ?>">
    
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /Users/maccato/Local Sites/nckh/app/public/resources/views/form.blade.php ENDPATH**/ ?>