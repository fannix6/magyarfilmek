const API_URL = (import.meta.env.VITE_API_URL || "").replace(/\/+$/, "");

export function getActorPhotoUrl(photoFileName) {
  if (!photoFileName) return "";
  return `${API_URL}/photos/${encodeURIComponent(photoFileName)}`;
}

export function getTrailerThumbnailUrl(trailerUrl) {
  if (!trailerUrl) return "";

  // YouTube URLs
  const youtubeMatch =
    trailerUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/]+)/i) ||
    trailerUrl.match(/youtube\.com\/embed\/([^&?/]+)/i);
  if (youtubeMatch?.[1]) {
    return `https://img.youtube.com/vi/${youtubeMatch[1]}/maxresdefault.jpg`;
  }

  // Generic trailer-page screenshot fallback
  // Works for Videa / OK.ru / random trailer pages too.
  return `https://s.wordpress.com/mshots/v1/${encodeURIComponent(trailerUrl)}?w=1280`;
}

export function getMovieCoverUrl(coverFileName) {
  if (!coverFileName) return "";
  return `${API_URL}/covers/${encodeURIComponent(coverFileName)}`;
}

export function getMovieTargetUrl(movie) {
  return movie?.watchlink || movie?.imdblink || "";
}
