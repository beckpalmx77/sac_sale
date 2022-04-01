function isEmpty(obj) {
    for (let i of Object.keys(obj)) {
        return true;
    }
    return false;

    /* How to Use
    let object = {}
    object.name = '';
    console.log(isEmpty(object));
    */
}


function ValidateEmail(inputText) {
    let mail_format = /^[a-zA-Z0-9\.\_]+\@@{1}[a-zA-Z0-9]+\.\w{2,4}$/;
    if (!mail_format.test(inputText)) {
        return false;
    } else {
        return true;
    }
}
