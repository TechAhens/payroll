<?php
/**
 * Form Field Component
 * Usage: include with $field array containing type, name, label, etc.
 */

$fieldType = $field['type'] ?? 'text';
$fieldName = $field['name'] ?? '';
$fieldId = $field['id'] ?? $fieldName;
$fieldLabel = $field['label'] ?? '';
$fieldValue = $field['value'] ?? '';
$fieldPlaceholder = $field['placeholder'] ?? '';
$fieldRequired = $field['required'] ?? false;
$fieldDisabled = $field['disabled'] ?? false;
$fieldReadonly = $field['readonly'] ?? false;
$fieldClass = $field['class'] ?? 'form-input';
$fieldHelp = $field['help'] ?? '';
$fieldError = $field['error'] ?? '';
$fieldOptions = $field['options'] ?? [];
$fieldAttributes = $field['attributes'] ?? [];
?>

<div class="form-field <?php echo $field['wrapper_class'] ?? ''; ?>">
    <?php if ($fieldLabel): ?>
        <label for="<?php echo htmlspecialchars($fieldId); ?>" 
               class="block text-sm font-medium text-gray-700 mb-2">
            <?php echo htmlspecialchars($fieldLabel); ?>
            <?php if ($fieldRequired): ?>
                <span class="text-red-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    
    <?php switch ($fieldType): 
        case 'text':
        case 'email':
        case 'password':
        case 'number':
        case 'date':
        case 'time':
        case 'url':
        case 'tel': ?>
            <input type="<?php echo $fieldType; ?>" 
                   name="<?php echo htmlspecialchars($fieldName); ?>" 
                   id="<?php echo htmlspecialchars($fieldId); ?>"
                   value="<?php echo htmlspecialchars($fieldValue); ?>"
                   placeholder="<?php echo htmlspecialchars($fieldPlaceholder); ?>"
                   class="<?php echo $fieldClass; ?> <?php echo $fieldError ? 'border-red-300' : ''; ?>"
                   <?php if ($fieldRequired): ?>required<?php endif; ?>
                   <?php if ($fieldDisabled): ?>disabled<?php endif; ?>
                   <?php if ($fieldReadonly): ?>readonly<?php endif; ?>
                   <?php foreach ($fieldAttributes as $attr => $value): ?>
                       <?php echo htmlspecialchars($attr); ?>="<?php echo htmlspecialchars($value); ?>"
                   <?php endforeach; ?>>
            <?php break; ?>
        
        <?php case 'textarea': ?>
            <textarea name="<?php echo htmlspecialchars($fieldName); ?>" 
                      id="<?php echo htmlspecialchars($fieldId); ?>"
                      placeholder="<?php echo htmlspecialchars($fieldPlaceholder); ?>"
                      class="<?php echo $fieldClass; ?> <?php echo $fieldError ? 'border-red-300' : ''; ?>"
                      rows="<?php echo $field['rows'] ?? 3; ?>"
                      <?php if ($fieldRequired): ?>required<?php endif; ?>
                      <?php if ($fieldDisabled): ?>disabled<?php endif; ?>
                      <?php if ($fieldReadonly): ?>readonly<?php endif; ?>><?php echo htmlspecialchars($fieldValue); ?></textarea>
            <?php break; ?>
        
        <?php case 'select': ?>
            <select name="<?php echo htmlspecialchars($fieldName); ?>" 
                    id="<?php echo htmlspecialchars($fieldId); ?>"
                    class="<?php echo $fieldClass; ?> <?php echo $fieldError ? 'border-red-300' : ''; ?>"
                    <?php if ($fieldRequired): ?>required<?php endif; ?>
                    <?php if ($fieldDisabled): ?>disabled<?php endif; ?>
                    <?php if (isset($field['multiple']) && $field['multiple']): ?>multiple<?php endif; ?>>
                <?php if (!$fieldRequired && !isset($field['multiple'])): ?>
                    <option value="">Select <?php echo htmlspecialchars($fieldLabel); ?></option>
                <?php endif; ?>
                <?php foreach ($fieldOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['value']); ?>"
                            <?php if ($option['value'] == $fieldValue || (is_array($fieldValue) && in_array($option['value'], $fieldValue))): ?>selected<?php endif; ?>>
                        <?php echo htmlspecialchars($option['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php break; ?>
        
        <?php case 'checkbox': ?>
            <div class="flex items-center">
                <input type="checkbox" 
                       name="<?php echo htmlspecialchars($fieldName); ?>" 
                       id="<?php echo htmlspecialchars($fieldId); ?>"
                       value="<?php echo htmlspecialchars($field['checkbox_value'] ?? '1'); ?>"
                       class="form-checkbox"
                       <?php if ($fieldValue): ?>checked<?php endif; ?>
                       <?php if ($fieldRequired): ?>required<?php endif; ?>
                       <?php if ($fieldDisabled): ?>disabled<?php endif; ?>>
                <?php if ($fieldLabel): ?>
                    <label for="<?php echo htmlspecialchars($fieldId); ?>" class="ml-2 text-sm text-gray-700">
                        <?php echo htmlspecialchars($fieldLabel); ?>
                    </label>
                <?php endif; ?>
            </div>
            <?php break; ?>
        
        <?php case 'file': ?>
            <input type="file" 
                   name="<?php echo htmlspecialchars($fieldName); ?>" 
                   id="<?php echo htmlspecialchars($fieldId); ?>"
                   class="<?php echo $fieldClass; ?>"
                   <?php if ($fieldRequired): ?>required<?php endif; ?>
                   <?php if ($fieldDisabled): ?>disabled<?php endif; ?>
                   <?php if (isset($field['accept'])): ?>accept="<?php echo htmlspecialchars($field['accept']); ?>"<?php endif; ?>
                   <?php if (isset($field['multiple']) && $field['multiple']): ?>multiple<?php endif; ?>>
            <?php break; ?>
    <?php endswitch; ?>
    
    <?php if ($fieldHelp): ?>
        <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($fieldHelp); ?></p>
    <?php endif; ?>
    
    <?php if ($fieldError): ?>
        <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($fieldError); ?></p>
    <?php endif; ?>
</div>