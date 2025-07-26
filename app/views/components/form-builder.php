<?php
/**
 * Dynamic Form Builder Component
 * Usage: include with $form array containing fields configuration
 */

$formId = $form['id'] ?? 'dynamic-form';
$formAction = $form['action'] ?? '';
$formMethod = $form['method'] ?? 'POST';
$fields = $form['fields'] ?? [];
$submitLabel = $form['submit_label'] ?? 'Submit';
$cancelUrl = $form['cancel_url'] ?? null;
$csrfToken = $form['csrf_token'] ?? '';
?>

<form id="<?php echo $formId; ?>" action="<?php echo htmlspecialchars($formAction); ?>" method="<?php echo $formMethod; ?>" 
      class="space-y-6" <?php if (isset($form['enctype'])): ?>enctype="<?php echo $form['enctype']; ?>"<?php endif; ?>>
    
    <?php if ($csrfToken): ?>
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <?php endif; ?>
    
    <?php foreach ($fields as $field): ?>
        <div class="form-group <?php echo $field['wrapper_class'] ?? ''; ?>">
            <?php
            $fieldType = $field['type'] ?? 'text';
            $fieldName = $field['name'] ?? '';
            $fieldId = $field['id'] ?? $fieldName;
            $fieldLabel = $field['label'] ?? '';
            $fieldValue = $field['value'] ?? '';
            $fieldRequired = $field['required'] ?? false;
            $fieldPlaceholder = $field['placeholder'] ?? '';
            $fieldClass = $field['class'] ?? 'form-input';
            $fieldHelp = $field['help'] ?? '';
            $fieldOptions = $field['options'] ?? [];
            ?>
            
            <!-- Label -->
            <?php if ($fieldLabel): ?>
                <label for="<?php echo $fieldId; ?>" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo htmlspecialchars($fieldLabel); ?>
                    <?php if ($fieldRequired): ?>
                        <span class="text-red-500">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <!-- Field Input -->
            <?php switch ($fieldType): 
                case 'text':
                case 'email':
                case 'password':
                case 'number':
                case 'date':
                case 'time':
                case 'datetime-local':
                case 'url':
                case 'tel': ?>
                    <input type="<?php echo $fieldType; ?>" 
                           name="<?php echo $fieldName; ?>" 
                           id="<?php echo $fieldId; ?>"
                           value="<?php echo htmlspecialchars($fieldValue); ?>"
                           placeholder="<?php echo htmlspecialchars($fieldPlaceholder); ?>"
                           class="<?php echo $fieldClass; ?>"
                           <?php if ($fieldRequired): ?>required<?php endif; ?>
                           <?php if (isset($field['min'])): ?>min="<?php echo $field['min']; ?>"<?php endif; ?>
                           <?php if (isset($field['max'])): ?>max="<?php echo $field['max']; ?>"<?php endif; ?>
                           <?php if (isset($field['step'])): ?>step="<?php echo $field['step']; ?>"<?php endif; ?>
                           <?php if (isset($field['pattern'])): ?>pattern="<?php echo $field['pattern']; ?>"<?php endif; ?>
                           <?php if (isset($field['data_attributes'])): ?>
                               <?php foreach ($field['data_attributes'] as $attr => $value): ?>
                                   data-<?php echo $attr; ?>="<?php echo htmlspecialchars($value); ?>"
                               <?php endforeach; ?>
                           <?php endif; ?>>
                    <?php break; ?>
                
                <?php case 'textarea': ?>
                    <textarea name="<?php echo $fieldName; ?>" 
                              id="<?php echo $fieldId; ?>"
                              placeholder="<?php echo htmlspecialchars($fieldPlaceholder); ?>"
                              class="<?php echo $fieldClass; ?>"
                              rows="<?php echo $field['rows'] ?? 3; ?>"
                              <?php if ($fieldRequired): ?>required<?php endif; ?>><?php echo htmlspecialchars($fieldValue); ?></textarea>
                    <?php break; ?>
                
                <?php case 'select': ?>
                    <select name="<?php echo $fieldName; ?>" 
                            id="<?php echo $fieldId; ?>"
                            class="<?php echo $fieldClass; ?>"
                            <?php if ($fieldRequired): ?>required<?php endif; ?>
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
                               name="<?php echo $fieldName; ?>" 
                               id="<?php echo $fieldId; ?>"
                               value="<?php echo htmlspecialchars($field['checkbox_value'] ?? '1'); ?>"
                               class="form-checkbox"
                               <?php if ($fieldValue): ?>checked<?php endif; ?>
                               <?php if ($fieldRequired): ?>required<?php endif; ?>>
                        <?php if ($fieldLabel): ?>
                            <label for="<?php echo $fieldId; ?>" class="ml-2 text-sm text-gray-700">
                                <?php echo htmlspecialchars($fieldLabel); ?>
                            </label>
                        <?php endif; ?>
                    </div>
                    <?php break; ?>
                
                <?php case 'radio': ?>
                    <div class="space-y-2">
                        <?php foreach ($fieldOptions as $option): ?>
                            <div class="flex items-center">
                                <input type="radio" 
                                       name="<?php echo $fieldName; ?>" 
                                       id="<?php echo $fieldId; ?>_<?php echo $option['value']; ?>"
                                       value="<?php echo htmlspecialchars($option['value']); ?>"
                                       class="form-radio"
                                       <?php if ($option['value'] == $fieldValue): ?>checked<?php endif; ?>
                                       <?php if ($fieldRequired): ?>required<?php endif; ?>>
                                <label for="<?php echo $fieldId; ?>_<?php echo $option['value']; ?>" class="ml-2 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($option['label']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php break; ?>
                
                <?php case 'file': ?>
                    <input type="file" 
                           name="<?php echo $fieldName; ?>" 
                           id="<?php echo $fieldId; ?>"
                           class="<?php echo $fieldClass; ?>"
                           <?php if ($fieldRequired): ?>required<?php endif; ?>
                           <?php if (isset($field['accept'])): ?>accept="<?php echo $field['accept']; ?>"<?php endif; ?>
                           <?php if (isset($field['multiple']) && $field['multiple']): ?>multiple<?php endif; ?>>
                    <?php break; ?>
                
                <?php case 'hidden': ?>
                    <input type="hidden" 
                           name="<?php echo $fieldName; ?>" 
                           id="<?php echo $fieldId; ?>"
                           value="<?php echo htmlspecialchars($fieldValue); ?>">
                    <?php break; ?>
                
                <?php case 'custom': ?>
                    <?php if (isset($field['html'])): ?>
                        <?php echo $field['html']; ?>
                    <?php endif; ?>
                    <?php break; ?>
                
            <?php endswitch; ?>
            
            <!-- Help Text -->
            <?php if ($fieldHelp): ?>
                <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($fieldHelp); ?></p>
            <?php endif; ?>
            
            <!-- Error Message Placeholder -->
            <div class="error-message-container"></div>
        </div>
    <?php endforeach; ?>
    
    <!-- Form Actions -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
        <?php if ($cancelUrl): ?>
            <a href="<?php echo htmlspecialchars($cancelUrl); ?>" class="btn btn-outline">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary">
            <?php if (isset($form['submit_icon'])): ?>
                <i class="<?php echo htmlspecialchars($form['submit_icon']); ?> mr-2"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($submitLabel); ?>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('<?php echo $formId; ?>');
    if (form) {
        setupFormValidation(form);
        setupFormEnhancements(form);
    }
});

function setupFormValidation(form) {
    form.addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
        }
    });
}

function setupFormEnhancements(form) {
    // Auto-format inputs based on data attributes
    form.querySelectorAll('[data-format]').forEach(input => {
        const format = input.dataset.format;
        
        switch (format) {
            case 'currency':
                input.addEventListener('blur', function() {
                    const value = parseFloat(this.value) || 0;
                    this.value = value.toFixed(2);
                });
                break;
                
            case 'phone':
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 10);
                });
                break;
                
            case 'pan':
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                });
                break;
                
            case 'ifsc':
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                });
                break;
        }
    });
    
    // Dependent dropdowns
    form.querySelectorAll('[data-depends-on]').forEach(select => {
        const dependsOn = select.dataset.dependsOn;
        const parentSelect = form.querySelector(`[name="${dependsOn}"]`);
        
        if (parentSelect) {
            parentSelect.addEventListener('change', function() {
                updateDependentSelect(select, this.value);
            });
        }
    });
}

function updateDependentSelect(select, parentValue) {
    const endpoint = select.dataset.endpoint;
    if (!endpoint) return;
    
    // Clear current options
    select.innerHTML = '<option value="">Loading...</option>';
    select.disabled = true;
    
    fetch(`${endpoint}?parent_value=${parentValue}`)
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Select...</option>';
            
            if (data.success && data.options) {
                data.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.label;
                    select.appendChild(optionElement);
                });
            }
            
            select.disabled = false;
        })
        .catch(error => {
            console.error('Error loading dependent options:', error);
            select.innerHTML = '<option value="">Error loading options</option>';
            select.disabled = false;
        });
}

function validateForm(form) {
    // Clear previous errors
    form.querySelectorAll('.field-error').forEach(el => {
        el.classList.remove('field-error');
    });
    form.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
    
    let isValid = true;
    
    // Validate required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Custom validation rules
    form.querySelectorAll('[data-validate]').forEach(field => {
        const rules = field.dataset.validate.split('|');
        
        rules.forEach(rule => {
            const [ruleName, ruleValue] = rule.split(':');
            
            if (!validateField(field.value, ruleName, ruleValue)) {
                showFieldError(field, getValidationMessage(ruleName, ruleValue));
                isValid = false;
            }
        });
    });
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('field-error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message text-red-600 text-sm mt-1';
    errorDiv.textContent = message;
    
    const container = field.closest('.form-group').querySelector('.error-message-container');
    if (container) {
        container.appendChild(errorDiv);
    } else {
        field.parentNode.appendChild(errorDiv);
    }
}

function validateField(value, rule, ruleValue) {
    switch (rule) {
        case 'min':
            return value.length >= parseInt(ruleValue);
        case 'max':
            return value.length <= parseInt(ruleValue);
        case 'numeric':
            return !isNaN(value) && !isNaN(parseFloat(value));
        case 'pan':
            return /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value);
        case 'aadhaar':
            return /^\d{12}$/.test(value.replace(/\s/g, ''));
        case 'ifsc':
            return /^[A-Z]{4}0[A-Z0-9]{6}$/.test(value);
        default:
            return true;
    }
}

function getValidationMessage(rule, ruleValue) {
    const messages = {
        min: `Minimum ${ruleValue} characters required`,
        max: `Maximum ${ruleValue} characters allowed`,
        numeric: 'Please enter a valid number',
        pan: 'Please enter a valid PAN number',
        aadhaar: 'Please enter a valid Aadhaar number',
        ifsc: 'Please enter a valid IFSC code'
    };
    
    return messages[rule] || 'Invalid value';
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
</script>