function FormValidator(){FormValidator.prototype.validate=validateField}function validateField(a){var b,c=$(a).get(0).tagName.toLowerCase();if("input"==c)switch(b=$(a).attr("type"),b){case "text":case "hidden":b=validateTextField($(a).val());break;case "email":b=validateEmailField($(a).val());break;case "tel":b=validatePhoneField($(a).val(),$(a).attr("pattern"));break;default:throw"Unsupported form input field type. ["+b+"]";}else"select"==c&&(b=validateSelectField(a));return b}
function validateTextField(a){return isNonEmpty(a)}function validateEmailField(a){var b=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;return isNonEmpty(a)&&a.match(b)}function validatePhoneField(a,b){return isNonEmpty(a)&&a.match(b)}function validateSelectField(a){return 1==$(a).find("option:not(:disabled)").filter(":selected").size()}function isNonEmpty(a){return 0!=a.length};