/*
  Theme: Muin (Custom Licensed Version)
  © Banafsijy.com – Single Project License – Do Not Redistribute
*/

function contentSearch() {
    const searchView = document.querySelector('.view-search-content');
    const resultsCount = searchView.querySelector(".view-header");
    return {
        results: resultsCount.textContent
    }
}