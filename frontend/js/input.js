//Handles adding / removing a focsed class on focus or blur events of our inputs
const form = document.querySelector("#RegForm");
console.log(form);
const inputs = form.querySelectorAll(".Input__inputEl");
function toggleInputFocus(event) {
  if (event.type === "focus") {
    event.target.classList.add("-focused");
    event.target.parentElement.classList.add("-focused");
  } else {
    event.target.classList.remove("-focused");
    event.target.parentElement.classList.remove("-focused");
  }
}
inputs.forEach(i => {
  i.addEventListener("focus", toggleInputFocus);
  i.addEventListener("blur", toggleInputFocus);
});
