async function typeSentence(sentence, delay = 100) {
    const letters = sentence.split("");
    let i = 0;
    while(i < letters.length) {
        await waitForMs(delay);
        $("#feature-text").append(letters[i]);
        i++
    }
    return;
}

async function deleteSentence() {
    const sentence = $("#feature-text").html();
    const letters = sentence.split("");
    let i = 0;
    while(letters.length > 0) {
        await waitForMs(100);
        letters.pop();
        $("#feature-text").html(letters.join(""));
    }
}


function waitForMs(ms) {
    return new Promise(resolve => setTimeout(resolve, ms))
}

$( document ).ready(async function() {
    while (true) {
        await deleteSentence();
        await typeSentence("Analyse");
        await waitForMs(2000);
        await deleteSentence();
        await typeSentence("Understand");
        await waitForMs(2000);
        await deleteSentence();
        await typeSentence("Summarise");
        await waitForMs(2000);
        await deleteSentence();
    }
});

var i = 0;
var speed = 2;

function typeWriter(txt) {
  if (!txt || typeof txt !== 'string') {
    console.error("typeWriter: invalid input", txt);
    return;
  }

  if (i < txt.length) {
    document.getElementById("speechInput").innerHTML += txt.charAt(i);
    i++;
    setTimeout(() => typeWriter(txt), speed);
  }
}