(() => {
  if (!window.HimsSession?.isAuthenticated()) window.location.replace("/login");
})();
