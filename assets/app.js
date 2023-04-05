/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";
import "../node_modules/tom-select/dist/css/tom-select.bootstrap4.css"

// start the Stimulus application
import "./bootstrap";

import TomSelect from "tom-select";
import {fetchSurahById, fetchEditionByLanguage} from "./api/api";

const createTomSelect = (select) => {
    return new TomSelect(select, {
        plugins: {
          dropdown_input: {},
        },
        maxOptions: null,
      });
  };
  

const typeRadio = document.querySelector(".type-radio");
const languageSelect = document.querySelector(".language-select");
const reciterSelect = document.querySelector(".reciter-select");
const surahSelect = document.querySelector(".surah-select");
const verseFrominput = document.querySelector(".verse-from");
const verseToinput = document.querySelector(".verse-to");


if (surahSelect) {
    const reciterTomSelect = createTomSelect(reciterSelect);

    createTomSelect(languageSelect).on('change' ,(value) => {
      console.log(value)
      // fetchEditionByLanguage(parseInt(value) + 1, (surah) => {
      //   // Display surah info and change toVerse input max
      //   verseToinput.max = surah.numberOfAyahs
      // });
  });

    createTomSelect(surahSelect).on('change' ,(value) => {
        fetchSurahById(parseInt(value) + 1, (surah) => {
          // Display surah info and change toVerse input max
          verseToinput.max = surah.numberOfAyahs
          verseFrominput.max = surah.numberOfAyahs
        });
    });
}