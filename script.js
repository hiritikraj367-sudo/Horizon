const care = document.querySelector("#care");

function openLinkedCare() {
  if (window.location.hash === "#care") {
    care.open = true;
  }
}

document.querySelector('a[href="#care"]').addEventListener("click", () => {
  care.open = true;
});

window.addEventListener("hashchange", openLinkedCare);
openLinkedCare();
