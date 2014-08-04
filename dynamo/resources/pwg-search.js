/* Google custom search code */
google.load('search', '1', {language : 'en'});
google.setOnLoadCallback(function() {
  var customSearchControl = new google.search.CustomSearchControl('018021367961685880654:mdt584m83r4');
  customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
  var options = new google.search.DrawOptions();
  options.setSearchFormRoot('pwg-search-form');
  options.setAutoComplete(true);
  customSearchControl.draw('pwg-search-results', options);
}, true);
