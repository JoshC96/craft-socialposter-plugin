/**
 * @param execute
 * @returns {function(): (null|*|undefined)}
 */
function wrap(execute) {
    var hasThrown = false;
    return function () {
        if (hasThrown) {
            return null;
        }
        try {
            return execute();
        } catch (error) {
            hasThrown = true;
            return null;
        }
    }
}

var errorExec = wrap(function () {
    throw new Error('Error');
});
var resultExec = wrap(function () {
    return "Result";
});

console.log(typeof errorExec === "function" && errorExec()); // Should output null
console.log(typeof resultExec === "function" && resultExec()); // Should output "Result"