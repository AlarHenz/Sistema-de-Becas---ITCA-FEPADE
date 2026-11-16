import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { HashRouter, Link, Navigate, NavLink, Outlet, Route, Routes, useNavigate } from 'react-router-dom';
import { apiRequest } from './api.js';
import {
  AdminDashboard,
  BeneficiariesPage,
  BulkImportPage,
  InstitutionsPage,
  RequestsPage,
  ScholarshipsPage,
  SelectedPage,
  StudentApplicationPage,
  StudentDashboard,
} from './pages.jsx';

const AuthContext = createContext(null);

function useAuth() {
  return useContext(AuthContext);
}

function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiRequest('auth/session')
      .then(({ user: sessionUser }) => setUser(sessionUser))
      .catch(() => setUser(null))
      .finally(() => setLoading(false));
  }, []);

  const value = useMemo(() => ({
    user,
    setUser,
    loading,
    login: async (credentials) => {
      const result = await apiRequest('auth/login', { method: 'POST', body: credentials });
      setUser(result.user);
      return result.user;
    },
    logout: async () => {
      await apiRequest('auth/logout', { method: 'POST', body: {} });
      setUser(null);
    },
  }), [user, loading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

function homeFor(user) {
  return user?.role === 'administrador' ? '/admin' : '/student';
}

function LoadingScreen() {
  return <div className="loading-screen" role="status">Cargando sistema...</div>;
}

function AuthGate({ role, children }) {
  const { user, loading } = useAuth();
  if (loading) return <LoadingScreen />;
  if (!user) return <Navigate to="/login" replace />;
  if (role && user.role !== role) return <Navigate to={homeFor(user)} replace />;
  return children;
}

function LoginPage() {
  const { user, login, loading } = useAuth();
  const navigate = useNavigate();
  const [carnet, setCarnet] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  if (loading) return <LoadingScreen />;
  if (user) return <Navigate to={homeFor(user)} replace />;

  async function submit(event) {
    event.preventDefault();
    setError('');
    setSubmitting(true);
    try {
      const loggedInUser = await login({ carnet: carnet.trim(), password });
      navigate(homeFor(loggedInUser), { replace: true });
    } catch (requestError) {
      setError(requestError.message);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="login-page">
      <section className="login-brand">
        <div>
          <div className="login-brand-mark">ITCA FEPADE</div>
          <h1>Sistema de Gestión de Becas</h1>
          <p>Plataforma institucional para la gestión de solicitudes y seguimiento de becas.</p>
        </div>
      </section>
      <section className="login-panel">
        <form className="login-form" onSubmit={submit}>
          <span className="eyebrow">Portal institucional</span>
          <h2>Iniciar sesión</h2>
          <p className="subtitulo">Accede con tus credenciales institucionales.</p>
          {error && <div className="alert alert-error" role="alert">{error}</div>}
          <div className="form-group">
            <label htmlFor="carnet">Carnet</label>
            <input id="carnet" value={carnet} onChange={(event) => setCarnet(event.target.value)} autoComplete="username" required />
          </div>
          <div className="form-group">
            <label htmlFor="password">Contraseña</label>
            <input id="password" type="password" value={password} onChange={(event) => setPassword(event.target.value)} autoComplete="current-password" required />
          </div>
          <button className="btn btn-primario login-submit" type="submit" disabled={submitting}>
            {submitting ? 'Ingresando...' : 'Ingresar'}
          </button>
        </form>
      </section>
    </main>
  );
}

function PageFrame({ children, navigation, subtitle }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [logoutError, setLogoutError] = useState('');
  const [signingOut, setSigningOut] = useState(false);

  async function signOut() {
    setLogoutError('');
    setSigningOut(true);
    try {
      await logout();
      navigate('/login', { replace: true });
    } catch (error) {
      setLogoutError(error.message);
    } finally {
      setSigningOut(false);
    }
  }

  return (
    <div className="layout">
      <header className="header">
        <Link className="titulo-sistema" to={homeFor(user)}>ITCA FEPADE</Link>
        <div className="usuario-info">
          <span>{user.role === 'administrador' ? 'Administrador' : 'Estudiante'} · {user.name}</span>
          <button className="btn-logout" type="button" onClick={signOut} disabled={signingOut}>
            {signingOut ? 'Saliendo...' : 'Cerrar sesión'}
          </button>
        </div>
      </header>
      <aside className="sidebar">
        {subtitle && <div className="sidebar-brand"><strong>ITCA FEPADE</strong><small>{subtitle}</small></div>}
        <nav>{navigation}</nav>
      </aside>
      <main className="contenido">
        {logoutError && <div className="alert alert-error" role="alert">{logoutError}</div>}
        {children || <Outlet />}
      </main>
      <footer className="footer">Sistema de Gestión de Becas · ITCA FEPADE</footer>
    </div>
  );
}

function AdminLayout() {
  return (
    <AuthGate role="administrador">
      <PageFrame navigation={<>
        <NavLink end to="/admin">Inicio</NavLink>
        <NavLink to="/admin/solicitudes">Solicitudes</NavLink>
        <NavLink to="/admin/seleccionados">Seleccionados</NavLink>
        <NavLink to="/admin/becarios">Becarios</NavLink>
        <NavLink to="/admin/carga-masiva">Carga masiva</NavLink>
        <NavLink to="/admin/becas">Catálogo de becas</NavLink>
        <NavLink to="/admin/instituciones">Instituciones</NavLink>
      </>} />
    </AuthGate>
  );
}

function StudentLayout() {
  return (
    <AuthGate role="estudiante">
      <PageFrame
        subtitle="Portal del Estudiante"
        navigation={<>
          <NavLink end to="/student">Dashboard</NavLink>
          <NavLink to="/student/solicitud">Mi Solicitud</NavLink>
        </>}
      />
    </AuthGate>
  );
}

function AppRoutes() {
  const { user, loading } = useAuth();
  if (loading) return <LoadingScreen />;
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<Navigate to={user ? homeFor(user) : '/login'} replace />} />
      <Route path="/admin" element={<AdminLayout />}>
        <Route index element={<AdminDashboard />} />
        <Route path="solicitudes" element={<RequestsPage />} />
        <Route path="seleccionados" element={<SelectedPage />} />
        <Route path="becarios" element={<BeneficiariesPage />} />
        <Route path="carga-masiva" element={<BulkImportPage />} />
        <Route path="becas" element={<ScholarshipsPage />} />
        <Route path="instituciones" element={<InstitutionsPage />} />
      </Route>
      <Route path="/student" element={<StudentLayout />}>
        <Route index element={<StudentDashboard />} />
        <Route path="solicitud" element={<StudentApplicationPage />} />
      </Route>
      <Route path="*" element={<Navigate to={user ? homeFor(user) : '/login'} replace />} />
    </Routes>
  );
}

export default function App() {
  return (
    <HashRouter>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </HashRouter>
  );
}
