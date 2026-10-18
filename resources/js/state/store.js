export class Store {
  #estado;
  #suscriptores = new Set();

  constructor(inicial) {
    this.#estado = { ...inicial };
  }

  getState() {
    return this.#estado;
  }

  setState(cambios) {
    this.#estado = { ...this.#estado, ...cambios };
    this.#suscriptores.forEach((fn) => fn(this.#estado));
  }

  subscribe(fn) {
    this.#suscriptores.add(fn);
    return () => this.#suscriptores.delete(fn);
  }
}

export const store = new Store({
  items: [],
  pagina: 1,
  cargando: false,
  error: null,
});

