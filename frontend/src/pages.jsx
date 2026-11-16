import { useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { apiRequest, apiUrl } from './api.js';

function useApiData(route, params = {}) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [version, setVersion] = useState(0);
  const paramsKey = JSON.stringify(params);

  useEffect(() => {
    let active = true;
    setLoading(true);
    setError('');
    apiRequest(route, { params: JSON.parse(paramsKey) })
      .then((result) => {
        if (active) setData(result);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message);
      })
      .finally(() => {
        if (active) setLoading(false);
      });
    return () => { active = false; };
  }, [route, paramsKey, version]);

  return { data, setData, loading, error, setError, refresh: () => setVersion((value) => value + 1) };
}

function PageTitle({ title, subtitle, actions }) {
  return (
    <div className="page-heading">
      <div>
        <h1 className="page-title">{title}</h1>
        {subtitle && <p className="subtitulo">{subtitle}</p>}
      </div>
      {actions && <div className="page-actions">{actions}</div>}
    </div>
  );
}

function Alert({ type = 'error', children }) {
  if (!children) return null;
  return <div className={`alert alert-${type === 'success' ? 'exito' : 'error'}`} role={type === 'error' ? 'alert' : 'status'}>{children}</div>;
}

function LoadingMessage() {
  return <p className="muted-message" role="status">Cargando información...</p>;
}

function EmptyRow({ columns, children = 'No hay registros para mostrar.' }) {
  return <tr><td className="empty-row" colSpan={columns}>{children}</td></tr>;
}

function StatusBadge({ value }) {
  const normalized = String(value || 'borrador').toLowerCase();
  const label = normalized.replaceAll('_', ' ');
  return <span className={`badge badge-${normalized.replaceAll('_', '')}`}>{label.charAt(0).toUpperCase() + label.slice(1)}</span>;
}

function displayDate(value, withTime = false) {
  if (!value) return '—';
  const parsed = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(parsed.getTime())) return value;
  return new Intl.DateTimeFormat('es-SV', withTime
    ? { dateStyle: 'short', timeStyle: 'short' }
    : { dateStyle: 'short' }).format(parsed);
}

function useMutation() {
  const [pending, setPending] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  async function run(action, successMessage = 'Cambios guardados correctamente.') {
    setPending(true);
    setError('');
    setSuccess('');
    try {
      const result = await action();
      setSuccess(result?.message || successMessage);
      return result;
    } catch (requestError) {
      setError(requestError.message);
      return null;
    } finally {
      setPending(false);
    }
  }

  return { pending, error, success, setError, setSuccess, run };
}

const adminQuickLinks = [
  ['Solicitudes', 'total', '/admin/solicitudes'],
  ['Solicitudes pendientes', 'inReview', '/admin/solicitudes?status=en_revision'],
  ['Seleccionados', 'selected', '/admin/seleccionados'],
  ['Becarios', 'beneficiaries', '/admin/becarios'],
  ['Solicitudes incompletas', 'incomplete', '/admin/solicitudes?status=incompleta'],
];

export function AdminDashboard() {
  const { data, loading, error } = useApiData('admin/dashboard');
  return (
    <>
      <PageTitle title="Panel de gestión" subtitle="Resumen general de solicitudes y becas." />
      {error && <Alert>{error}</Alert>}
      <h2 className="section-title">Accesos rápidos</h2>
      {loading ? <LoadingMessage /> : data && (
        <div className="grid-accesos-rapidos">
          {adminQuickLinks.map(([label, key, href]) => (
            <Link className="tarjeta-acceso" to={href} key={key}>
              <span><span className="tarjeta-titulo">{label}</span><span className="tarjeta-numero">{data[key]}</span></span>
              <span className="tarjeta-enlace">Ver módulo →</span>
            </Link>
          ))}
        </div>
      )}
      {!loading && data && (
        <section className="card-estadisticas">
          <div className="estadisticas-header">
            <div><h2 className="estadisticas-titulo">Estadísticas</h2><p className="estadisticas-subtitulo">Estado actual del sistema.</p></div>
          </div>
          <div className="grid-estadisticas-cajas">
            {[
              ['Solicitudes recibidas', data.total],
              ['Completas', data.complete],
              ['Incompletas', data.incomplete],
              ['Seleccionados', data.selected],
              ['Becarios', data.beneficiaries],
            ].map(([label, count]) => <div className="caja-estadistica" key={label}><div className="caja-numero">{count}</div><div className="caja-etiqueta">{label}</div></div>)}
          </div>
        </section>
      )}
    </>
  );
}

const requestFilters = [
  ['todas', 'Todas'],
  ['en_revision', 'En revisión'],
  ['aprobada', 'Aprobadas'],
  ['rechazada', 'Rechazadas'],
  ['incompleta', 'Incompletas'],
  ['borrador', 'Borradores'],
];

function RequestEditor({ item, onSaved }) {
  const mutation = useMutation();
  const [state, setState] = useState(item.estado);
  const [observation, setObservation] = useState(item.observaciones_admin || '');

  async function submit(event) {
    event.preventDefault();
    const result = await mutation.run(
      () => apiRequest('admin/requests', { method: 'PUT', body: { id: item.id, state, observation } }),
      'Solicitud actualizada correctamente.',
    );
    if (result) onSaved();
  }

  return (
    <details className="inline-editor">
      <summary className="btn btn-secundario">Gestionar</summary>
      <form className="editor-form" onSubmit={submit}>
        <div className="form-group">
          <label htmlFor={`request-state-${item.id}`}>Estado</label>
          <select id={`request-state-${item.id}`} value={state} onChange={(event) => setState(event.target.value)}>
            {['borrador', 'en_revision', 'aprobada', 'rechazada', 'incompleta'].map((value) => <option value={value} key={value}>{value.replaceAll('_', ' ')}</option>)}
          </select>
        </div>
        <div className="form-group">
          <label htmlFor={`request-observation-${item.id}`}>Observaciones (se envían al estudiante)</label>
          <textarea id={`request-observation-${item.id}`} rows="3" value={observation} onChange={(event) => setObservation(event.target.value)} />
        </div>
        {mutation.error && <Alert>{mutation.error}</Alert>}
        {mutation.success && <Alert type="success">{mutation.success}</Alert>}
        <button className="btn btn-primario" type="submit" disabled={mutation.pending}>{mutation.pending ? 'Guardando...' : 'Guardar cambios'}</button>
      </form>
    </details>
  );
}

export function RequestsPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const requestedFilter = searchParams.get('status');
  const filter = requestFilters.some(([value]) => value === requestedFilter) ? requestedFilter : 'todas';
  const { data, loading, error, refresh } = useApiData('admin/requests', { status: filter });
  return (
    <>
      <PageTitle title="Gestión de Solicitudes" subtitle="Administra las solicitudes de beca de los estudiantes." />
      {error && <Alert>{error}</Alert>}
      <div className="filter-tabs" aria-label="Filtrar solicitudes">
        {requestFilters.map(([value, label]) => (
          <button type="button" className={`btn ${filter === value ? 'btn-primario' : 'btn-secundario'}`} onClick={() => setSearchParams(value === 'todas' ? {} : { status: value })} key={value}>
            {label} ({data?.counts?.[value] ?? 0})
          </button>
        ))}
      </div>
      {loading ? <LoadingMessage /> : (
        <div className="tabla-container">
          <table>
            <thead><tr><th>ID</th><th>Estudiante</th><th>Carnet</th><th>Especialidad</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
              {data?.items?.length ? data.items.map((item) => (
                <tr key={item.id}>
                  <td>#{item.id}</td><td>{item.nombre_completo}</td><td>{item.carnet}</td>
                  <td>{item.nombre_especialidad || 'N/A'}</td><td>{displayDate(item.fecha_solicitud)}</td>
                  <td><StatusBadge value={item.estado} /></td>
                  <td><RequestEditor item={item} onSaved={refresh} /></td>
                </tr>
              )) : <EmptyRow columns={7}>No hay solicitudes para mostrar.</EmptyRow>}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

export function SelectedPage() {
  const { data, loading, error, refresh } = useApiData('admin/selected');
  const mutation = useMutation();

  async function assign(event, item) {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const result = await mutation.run(
      () => apiRequest('admin/selected', {
        method: 'PUT',
        body: { applicationId: item.id_solicitud, scholarshipId: formData.get('scholarshipId') },
      }),
      'La beca asignada se actualizó correctamente.',
    );
    if (result) refresh();
  }

  return (
    <>
      <PageTitle title="Listado de Aspirantes Seleccionados" subtitle={`Aspirantes en revisión o aprobados (Total: ${data?.items?.length ?? 0})`} />
      {error && <Alert>{error}</Alert>}
      {mutation.error && <Alert>{mutation.error}</Alert>}
      {mutation.success && <Alert type="success">{mutation.success}</Alert>}
      {loading ? <LoadingMessage /> : (
        <div className="tabla-container">
          <table>
            <thead><tr><th>ID</th><th>Estudiante</th><th>Carnet</th><th>Especialidad</th><th>Fecha</th><th>Prioridad</th><th>Estado</th><th>Beca</th><th>Acciones</th></tr></thead>
            <tbody>
              {data?.items?.length ? data.items.map((item) => (
                <tr key={item.id_solicitud}>
                  <td>#{item.id_solicitud}</td><td>{item.nombre_completo}</td><td>{item.carnet}</td><td>{item.nombre_especialidad || 'N/A'}</td>
                  <td>{displayDate(item.fecha_solicitud)}</td><td><StatusBadge value={item.prioridad || 'media'} /></td><td><StatusBadge value={item.estado} /></td>
                  <td>{item.nombre_beca || 'Sin asignar'}{item.estado_beca === 'inactiva' && <small className="muted-inline"> (inactiva)</small>}</td>
                  <td>
                    <details className="inline-editor">
                      <summary className="btn btn-primario">Gestionar</summary>
                      <form className="editor-form compact-form" onSubmit={(event) => assign(event, item)}>
                        <label htmlFor={`scholarship-${item.id_solicitud}`}>Beca</label>
                        <select id={`scholarship-${item.id_solicitud}`} name="scholarshipId" defaultValue={item.id_beca || ''}>
                          <option value="">Sin asignar</option>
                          {item.estado_beca === 'inactiva' && <option value={item.id_beca}>{item.nombre_beca} (inactiva)</option>}
                          {data.scholarships.map((scholarship) => <option value={scholarship.id} key={scholarship.id}>{scholarship.nombre_beca}</option>)}
                        </select>
                        <button className="btn btn-secundario" type="submit" disabled={mutation.pending}>Guardar beca</button>
                      </form>
                    </details>
                  </td>
                </tr>
              )) : <EmptyRow columns={9}>No hay aspirantes seleccionados por el momento.</EmptyRow>}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

function downloadCsv(filename, rows) {
  const csv = rows.map((row) => row.map((value) => `"${String(value ?? '').replaceAll('"', '""')}"`).join(',')).join('\r\n');
  const url = URL.createObjectURL(new Blob(['\uFEFF', csv], { type: 'text/csv;charset=utf-8;' }));
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

export function BeneficiariesPage() {
  const { data, loading, error } = useApiData('admin/beneficiaries');
  const items = data?.items || [];
  const columns = ['ID Solicitud', 'Estudiante', 'Carnet', 'Correo ITCA', 'Especialidad', 'Beca Asignada', 'Fecha Aprobación'];
  const exportCsv = () => downloadCsv('listado_becados.csv', [
    columns,
    ...items.map((item) => [item.id_solicitud, item.nombre_completo, item.carnet, item.correo_institucional, item.nombre_especialidad || 'N/A', item.nombre_beca || 'Sin asignar', displayDate(item.fecha_solicitud)]),
  ]);
  const openMail = () => {
    const emails = items.map((item) => item.correo_institucional).filter(Boolean);
    if (!emails.length) {
      window.alert('No hay correos para generar.');
      return;
    }
    window.location.href = `mailto:?bcc=${encodeURIComponent(emails.join(','))}&subject=${encodeURIComponent('Notificación de Beca ITCA-FEPADE')}`;
  };

  return (
    <>
      <PageTitle
        title="Aspirantes con Beca (Becarios)"
        subtitle={`Total de becados aprobados: ${items.length}`}
        actions={<><button className="btn btn-primario" type="button" onClick={exportCsv} disabled={loading}>Generar Excel (CSV)</button><button className="btn btn-secundario" type="button" onClick={openMail} disabled={loading}>Generar lista de correos</button></>}
      />
      {error && <Alert>{error}</Alert>}
      {loading ? <LoadingMessage /> : (
        <div className="tabla-container">
          <table>
            <thead><tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr></thead>
            <tbody>
              {items.length ? items.map((item) => (
                <tr key={item.id_solicitud}><td>#{item.id_solicitud}</td><td>{item.nombre_completo}</td><td>{item.carnet}</td><td>{item.correo_institucional}</td><td>{item.nombre_especialidad || 'N/A'}</td><td>{item.nombre_beca || 'Sin asignar'}</td><td>{displayDate(item.fecha_solicitud)}</td></tr>
              )) : <EmptyRow columns={columns.length}>No hay estudiantes con beca aprobada aún.</EmptyRow>}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

export function BulkImportPage() {
  const mutation = useMutation();
  const [file, setFile] = useState(null);
  const [result, setResult] = useState(null);

  async function submit(event) {
    event.preventDefault();
    if (!file) {
      mutation.setError('Selecciona un archivo CSV para cargar.');
      return;
    }
    const formData = new FormData();
    formData.append('file', file);
    const imported = await mutation.run(
      () => apiRequest('admin/import', { method: 'POST', body: formData }),
      'Archivo procesado e importado con éxito.',
    );
    if (imported) setResult(imported);
  }

  return (
    <>
      <PageTitle title="Carga Masiva de Aspirantes (.csv)" subtitle="Importa registros de estudiantes desde una plantilla CSV." />
      <Alert>{mutation.error}</Alert>
      {mutation.success && <Alert type="success">{mutation.success}</Alert>}
      <div className="import-grid">
        <section className="tarjeta">
          <h2 className="section-title">Seleccionar archivo</h2>
          <p className="subtitulo">El CSV debe incluir carnet, nombre completo y correo institucional como campos obligatorios.</p>
          <form onSubmit={submit}>
            <div className="form-group">
              <label htmlFor="csv-file">Archivo CSV</label>
              <input id="csv-file" type="file" accept=".csv,text/csv" onChange={(event) => setFile(event.target.files?.[0] || null)} />
              {file && <small className="selected-file">{file.name}</small>}
            </div>
            <div className="button-row">
              <button className="btn btn-primario" type="submit" disabled={mutation.pending}>{mutation.pending ? 'Procesando...' : 'Cargar aspirantes'}</button>
              <a className="btn btn-secundario" href={apiUrl('admin/template')}>Descargar plantilla</a>
            </div>
          </form>
        </section>
        <section className="tarjeta">
          <h2 className="section-title">Columnas de la plantilla</h2>
          <ol className="column-list"><li>Código / Carnet</li><li>Nombre completo</li><li>Teléfono fijo (opcional)</li><li>Teléfono móvil (opcional)</li><li>Correo ITCA</li><li>Correo personal (opcional)</li></ol>
          <p className="muted-message">La contraseña inicial de las cuentas importadas es <strong>123456</strong>.</p>
        </section>
      </div>
      {result && (
        <section className="import-results">
          <div className="tarjeta result-card">
            <h2 className="section-title">Resumen de importación</h2>
            <p className="subtitulo">Archivo: <strong>{result.filename}</strong></p>
            <div className="result-metrics">
              <div><strong>{result.total}</strong><span>Evaluados</span></div>
              <div><strong>{result.inserted}</strong><span>Insertados</span></div>
              <div><strong>{result.duplicates}</strong><span>Duplicados</span></div>
              <div><strong>{result.errors}</strong><span>Con errores</span></div>
            </div>
            {result.log.length > 0 && (
              <details className="result-details"><summary>Ver errores y duplicados ({result.log.length})</summary>
                <div className="tabla-container"><table><thead><tr><th>Fila</th><th>Carnet</th><th>Detalle</th></tr></thead><tbody>
                  {result.log.map((entry, index) => <tr key={`${entry.row}-${index}`}><td>{entry.row}</td><td>{entry.carnet}</td><td>{entry.message}</td></tr>)}
                </tbody></table></div>
              </details>
            )}
            {result.added.length > 0 && (
              <details className="result-details"><summary>Estudiantes incorporados ({result.added.length})</summary>
                <div className="tabla-container"><table><thead><tr><th>Carnet</th><th>Nombre</th><th>Correo</th><th>Móvil</th></tr></thead><tbody>
                  {result.added.map((student) => <tr key={student.carnet}><td>{student.carnet}</td><td>{student.name}</td><td>{student.email}</td><td>{student.mobile}</td></tr>)}
                </tbody></table></div>
              </details>
            )}
          </div>
        </section>
      )}
    </>
  );
}

export function ScholarshipsPage() {
  const { data, loading, error, refresh } = useApiData('admin/scholarships');
  const mutation = useMutation();
  async function submitNew(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = new FormData(form);
    const result = await mutation.run(() => apiRequest('admin/scholarships', {
      method: 'POST',
      body: { name: values.get('name'), description: values.get('description') },
    }), 'Beca agregada correctamente.');
    if (result) {
      form.reset();
      refresh();
    }
  }
  async function toggle(id) {
    const result = await mutation.run(() => apiRequest('admin/scholarships', { method: 'PATCH', params: { id }, body: {} }), 'Estado de la beca actualizado.');
    if (result) refresh();
  }
  async function remove(item) {
    if (!window.confirm(`¿Seguro que deseas eliminar la beca "${item.nombre_beca}"?`)) return;
    const result = await mutation.run(() => apiRequest('admin/scholarships', { method: 'DELETE', params: { id: item.id } }), 'Beca eliminada correctamente.');
    if (result) refresh();
  }
  async function edit(event, item) {
    event.preventDefault();
    const values = new FormData(event.currentTarget);
    const result = await mutation.run(() => apiRequest('admin/scholarships', {
      method: 'PUT',
      body: { id: item.id, name: values.get('name'), description: values.get('description') },
    }), 'Beca actualizada correctamente.');
    if (result) refresh();
  }

  return (
    <>
      <PageTitle title="Catálogo de Becas" subtitle="Administra los tipos de beca disponibles en el sistema." />
      {error && <Alert>{error}</Alert>}
      {mutation.error && <Alert>{mutation.error}</Alert>}
      {mutation.success && <Alert type="success">{mutation.success}</Alert>}
      <section className="tarjeta form-card">
        <h2 className="section-title">Agregar Nueva Beca</h2>
        <form className="form-inline" onSubmit={submitNew}>
          <div className="form-group"><label htmlFor="scholarship-name">Nombre de la beca *</label><input id="scholarship-name" name="name" required /></div>
          <div className="form-group"><label htmlFor="scholarship-description">Descripción (opcional)</label><input id="scholarship-description" name="description" /></div>
          <button className="btn btn-primario" type="submit" disabled={mutation.pending}>Agregar beca</button>
        </form>
      </section>
      {loading ? <LoadingMessage /> : (
        <div className="tabla-container"><table><thead><tr><th>ID</th><th>Nombre de la beca</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
          {data?.items?.length ? data.items.map((item) => (
            <tr key={item.id}>
              <td>#{item.id}</td><td>{item.nombre_beca}</td><td>{item.descripcion || '—'}</td><td><StatusBadge value={item.estado} /></td>
              <td className="table-actions">
                <details className="inline-editor"><summary className="btn btn-secundario">Editar</summary>
                  <form className="editor-form" onSubmit={(event) => edit(event, item)}>
                    <div className="form-group"><label>Nombre</label><input name="name" defaultValue={item.nombre_beca} required /></div>
                    <div className="form-group"><label>Descripción</label><input name="description" defaultValue={item.descripcion || ''} /></div>
                    <button className="btn btn-primario" type="submit" disabled={mutation.pending}>Guardar cambios</button>
                  </form>
                </details>
                <button className="btn btn-secundario" type="button" onClick={() => toggle(item.id)} disabled={mutation.pending}>{item.estado === 'activa' ? 'Desactivar' : 'Activar'}</button>
                <button className="btn btn-danger" type="button" onClick={() => remove(item)} disabled={mutation.pending}>Eliminar</button>
              </td>
            </tr>
          )) : <EmptyRow columns={5}>No hay becas registradas.</EmptyRow>}
        </tbody></table></div>
      )}
    </>
  );
}

export function InstitutionsPage() {
  const { data, loading, error, refresh } = useApiData('admin/institutions');
  const mutation = useMutation();

  async function addInstitution(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = new FormData(form);
    const result = await mutation.run(() => apiRequest('admin/institutions', { method: 'POST', body: { name: values.get('name') } }), 'Institución agregada correctamente.');
    if (result) {
      form.reset();
      refresh();
    }
  }
  async function addSpecialty(event, institutionId) {
    event.preventDefault();
    const form = event.currentTarget;
    const values = new FormData(form);
    const result = await mutation.run(() => apiRequest('admin/institutions', {
      method: 'PUT',
      body: { institutionId, name: values.get('name') },
    }), 'Especialidad agregada correctamente.');
    if (result) {
      form.reset();
      refresh();
    }
  }
  async function removeSpecialty(specialty) {
    if (!window.confirm(`¿Eliminar la especialidad "${specialty.nombre_especialidad}"?`)) return;
    const result = await mutation.run(() => apiRequest('admin/institutions', { method: 'DELETE', params: { id: specialty.id } }), 'Especialidad eliminada.');
    if (result) refresh();
  }

  const specialtiesByInstitution = useMemo(() => {
    const grouped = new Map();
    (data?.specialties || []).forEach((specialty) => {
      const values = grouped.get(String(specialty.id_institucion)) || [];
      values.push(specialty);
      grouped.set(String(specialty.id_institucion), values);
    });
    return grouped;
  }, [data]);

  return (
    <>
      <PageTitle title="Instituciones y Especialidades" subtitle="Configura las instituciones y sus especialidades académicas." />
      {error && <Alert>{error}</Alert>}
      {mutation.error && <Alert>{mutation.error}</Alert>}
      {mutation.success && <Alert type="success">{mutation.success}</Alert>}
      <section className="tarjeta form-card">
        <h2 className="section-title">Agregar Nueva Institución</h2>
        <form className="form-inline" onSubmit={addInstitution}>
          <div className="form-group"><label htmlFor="institution-name">Nombre de la institución *</label><input id="institution-name" name="name" required /></div>
          <button className="btn btn-primario" type="submit" disabled={mutation.pending}>Agregar institución</button>
        </form>
      </section>
      {loading ? <LoadingMessage /> : (
        <div className="institution-list">
          {data?.institutions?.length ? data.institutions.map((institution) => {
            const specialties = specialtiesByInstitution.get(String(institution.id)) || [];
            return (
              <section className="tarjeta institution-card" key={institution.id}>
                <div className="institution-heading"><h2>{institution.nombre_institucion}</h2><span className="badge badge-borrador">{specialties.length} especialidades</span></div>
                <form className="form-inline specialty-form" onSubmit={(event) => addSpecialty(event, institution.id)}>
                  <div className="form-group"><label htmlFor={`specialty-${institution.id}`}>Nueva especialidad</label><input id={`specialty-${institution.id}`} name="name" placeholder="Ej: Ingeniería en Sistemas" required /></div>
                  <button className="btn btn-primario" type="submit" disabled={mutation.pending}>Agregar especialidad</button>
                </form>
                {specialties.length > 0 && (
                  <ul className="specialty-list">
                    {specialties.map((specialty) => <li key={specialty.id}><span>{specialty.nombre_especialidad}</span><button className="btn btn-danger" type="button" onClick={() => removeSpecialty(specialty)} disabled={mutation.pending}>Eliminar</button></li>)}
                  </ul>
                )}
              </section>
            );
          }) : <section className="tarjeta"><p>No hay instituciones registradas.</p></section>}
        </div>
      )}
    </>
  );
}

export function StudentDashboard() {
  const { data, loading, error } = useApiData('student/dashboard');
  const application = data?.application;
  return (
    <>
      <PageTitle title="Mi Portal" subtitle="Este es el resumen de tu solicitud de beca." />
      {error && <Alert>{error}</Alert>}
      {loading ? <LoadingMessage /> : application ? (
        <div className="student-dashboard-grid">
          <section className="tarjeta">
            <h2 className="section-title">Estado de tu solicitud</h2>
            <p className="status-line">Estado actual: <StatusBadge value={application.estado} /></p>
            <p className="subtitulo">Fecha: {displayDate(application.fecha_solicitud)}</p>
            <Link className="btn btn-primario" to="/student/solicitud">Ver / Continuar Solicitud</Link>
          </section>
          <section className="tarjeta">
            <h2 className="section-title">Notificaciones</h2>
            {data.notifications.length ? <ul className="notification-list">
              {data.notifications.map((notification) => <li key={notification.id}><span>{notification.mensaje}</span><small>{displayDate(notification.fecha_creacion, true)}</small></li>)}
            </ul> : <p className="muted-message">No tienes notificaciones pendientes.</p>}
          </section>
        </div>
      ) : (
        <section className="tarjeta empty-state">
          <h2>No tienes ninguna solicitud activa</h2>
          <p>Comienza tu proceso de solicitud de beca ahora mismo.</p>
          <Link className="btn btn-primario" to="/student/solicitud">Iniciar Solicitud</Link>
        </section>
      )}
    </>
  );
}

export function StudentApplicationPage() {
  const { data, loading, error, refresh } = useApiData('student/application');
  const mutation = useMutation();
  const [specialtyId, setSpecialtyId] = useState('');
  const [file, setFile] = useState(null);

  useEffect(() => {
    if (data?.application) setSpecialtyId(String(data.application.id_especialidad || ''));
  }, [data]);

  async function submit(event, action) {
    event.preventDefault();
    if (!specialtyId) {
      mutation.setError('Debes seleccionar una especialidad de interés.');
      return;
    }
    const formData = new FormData();
    formData.append('id_especialidad', specialtyId);
    formData.append('accion', action);
    if (file) formData.append('documento_dui', file);
    const result = await mutation.run(
      () => apiRequest('student/application', { method: 'POST', body: formData }),
    );
    if (result) {
      setFile(null);
      const input = document.getElementById('dui-document');
      if (input) input.value = '';
      refresh();
    }
  }

  return (
    <>
      <PageTitle title="Mi Solicitud de Beca" subtitle="Completa los datos para iniciar tu proceso." />
      {error && <Alert>{error}</Alert>}
      {mutation.error && <Alert>{mutation.error}</Alert>}
      {mutation.success && <Alert type="success">{mutation.success}</Alert>}
      {loading ? <LoadingMessage /> : data && (
        <>
          {data.application?.observaciones_admin && <Alert>Observación del Administrador: {data.application.observaciones_admin}</Alert>}
          <form className="application-form" onSubmit={(event) => submit(event, 'enviar')}>
            <section className="tarjeta application-card">
              <h2>1. Datos Personales</h2>
              <div className="personal-grid">
                <div className="form-group"><label htmlFor="student-name">Nombre completo</label><input id="student-name" value={data.user?.nombre_completo || ''} readOnly /></div>
                <div className="form-group"><label htmlFor="student-carnet">Carnet</label><input id="student-carnet" value={data.user?.carnet || ''} readOnly /></div>
                <div className="form-group"><label htmlFor="student-email">Correo institucional</label><input id="student-email" type="email" value={data.user?.correo_institucional || ''} readOnly /></div>
                <div className="form-group"><label htmlFor="student-phone">Teléfono móvil</label><input id="student-phone" value={data.user?.telefono_movil || ''} readOnly /></div>
              </div>
            </section>
            <section className="tarjeta application-card">
              <h2>2. Información Académica</h2>
              <div className="form-group">
                <label htmlFor="application-specialty">Especialidad de interés *</label>
                <select id="application-specialty" value={specialtyId} onChange={(event) => setSpecialtyId(event.target.value)} required>
                  <option value="">-- Selecciona una especialidad --</option>
                  {data.specialties.map((specialty) => <option value={specialty.id} key={specialty.id}>{specialty.nombre_institucion} - {specialty.nombre_especialidad}</option>)}
                </select>
              </div>
            </section>
            <section className="tarjeta application-card">
              <h2>3. Documentos</h2>
              <div className="form-group">
                <label htmlFor="dui-document">Documento de Identidad (DUI)</label>
                <input id="dui-document" type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={(event) => setFile(event.target.files?.[0] || null)} />
                <small className="muted-message">Formatos permitidos: PDF, JPG, PNG. Máximo 5MB.</small>
              </div>
              {data.document && (
                <p className="document-current"><strong>Archivo actual:</strong> <a href={apiUrl('student/document', { id: data.document.id })} target="_blank" rel="noreferrer">Ver documento subido</a> <StatusBadge value={data.document.estado_documento} /></p>
              )}
            </section>
            <div className="application-actions">
              <button className="btn btn-secundario" type="button" disabled={mutation.pending} onClick={(event) => submit(event, 'borrador')}>Guardar Borrador</button>
              <button className="btn btn-primario" type="submit" disabled={mutation.pending}>{mutation.pending ? 'Guardando...' : 'Enviar Solicitud'}</button>
            </div>
          </form>
        </>
      )}
    </>
  );
}
