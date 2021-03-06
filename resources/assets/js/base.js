// Escape jQuery selectors
function esc_selector(selector) {
    return selector.replace( /(:|\.|\[|\]|,)/g, "\\$1" );
}

jQuery.fn.clearForm = function() {
    // http://stackoverflow.com/questions/6364289/clear-form-fields-with-jquery
    $(this).find('input').not(':button, :submit, :reset, :hidden')
        .val('')
        .removeAttr('checked')
        .removeAttr('selected');

    return this;
};

// Output helpful console message
// console.log('%cPolr', 'font-size:5em;color:green');
// console.log('%cNeed help? Open a ticket: https://github.com/cydrobolt/polr', 'color:blue');
// console.log('%cDocs: https://docs.polr.me', 'color:blue');

// Stats data
var dayData = {};
var refererData = {};
var countryData = {};

//Set up the Polr object
var polr = {
    home: {},
    stats: {
        leftBound: '',
        rightBound: ''
    }
};
