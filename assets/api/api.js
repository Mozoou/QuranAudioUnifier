import axios from 'axios';

// Make a request for a user with a given ID
const fetchSurahById = (id, success) => {
    axios.get('/api/fetch/surah/' + id)
    .then(function (response) {
      // handle success
      success(response.data.surah)
    })
    .catch(function (error) {
      // handle error
      console.log(error);
    })
    .finally(function () {
      // always executed
    });  
}

const fetchEditionByLanguage = (language, success) => {
  axios.get('api/fetch/editions/{language}' + language)
  .then(function (response) {
    // handle success
    success(response.data.editions)
  })
  .catch(function (error) {
    // handle error
    console.log(error);
  })
  .finally(function () {
    // always executed
  });  
}

export {fetchSurahById, fetchEditionByLanguage};