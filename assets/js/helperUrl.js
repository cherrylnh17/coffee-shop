function getImageUrl(imagePath) {
    if (!imagePath) return '';
    
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    const cleanBaseUrl = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';
    const cleanImagePath = imagePath.startsWith('/') ? imagePath.substring(1) : imagePath;
    
    return cleanBaseUrl + cleanImagePath;
}