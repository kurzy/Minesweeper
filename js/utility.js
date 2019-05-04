function check(form) {
    var dim = form.dimensions.value;
    var shape = form.cell_shape.value;
    if (dim == null || shape == null || dim == "" || shape == "") {
        alert("Please enter a dimension, cell shape and colour mode.");
        return false;
    }
    else if (dim > 26 || dim < 4) {
        alert("Please enter a dimension between 4..26");
        form.dimensions.focus();
        return false;
    }
    else if (isNaN(dim) || dim % 1 != 0) {
        alert("Dimension must be a whole number from 4..26.");
        return false;
    }
    return true;
}