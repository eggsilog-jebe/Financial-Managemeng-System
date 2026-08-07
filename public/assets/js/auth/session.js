/* Frontend-only session adapter. Replace its public methods with Laravel/Breeze calls later. */
(() => {
  const storageKey = "himsMainSession";
  const read = () => {
    try {
      const raw = sessionStorage.getItem(storageKey);
      const session = raw ? JSON.parse(raw) : null;
      return session?.authenticated === true ? session : null;
    } catch { return null; }
  };
  const create = ({ name, email, role }) => {
    const session = { authenticated: true, user: { name, email, role }, createdAt: new Date().toISOString() };
    sessionStorage.setItem(storageKey, JSON.stringify(session));
    return session;
  };
  const clear = () => sessionStorage.removeItem(storageKey);
  window.HimsSession = Object.freeze({ storageKey, read, create, clear, isAuthenticated: () => Boolean(read()), getUser: () => read()?.user || null });
})();
