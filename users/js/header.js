function navigateToPage(selectElement) {
  const selectedValue = selectElement.value;
  if (selectedValue) {
    window.location.href = selectedValue; // Navigate to the selected page
  }
}
