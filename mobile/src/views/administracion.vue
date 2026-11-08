<template>
  <ion-page>
    <ion-header>
      <AppHeader titulo="Administracion" />
    </ion-header>

    <ion-content class="ion-padding">

    <ion-fab slot="fixed" vertical="bottom" horizontal="end">
        <ion-fab-button color="primary" @click="accionAgregar">
          <ion-icon :icon="add"></ion-icon>
        </ion-fab-button>
    </ion-fab>

    <div v-if="tabActivo === 'productos'">
        <h2 style="color: white;">Productos</h2>
       <ion-list>
          <ion-item-sliding v-for="producto in listaProductos" :key="producto.id">
            <ion-item color="primary">
              <ion-label>
                <h2>{{ producto.nombre }}</h2>
                <p>₡{{ producto.precio }} - {{ producto.estado }}</p>
              </ion-label>
            </ion-item>
            <ion-item-options side="end">
              <ion-item-option color="tertiary" @click="editarProducto(producto)">
                <ion-icon slot="icon-only" :icon="pencil"></ion-icon>
              </ion-item-option>
              <ion-item-option color="danger" @click="eliminarProducto(producto.id)">
                <ion-icon slot="icon-only" :icon="trash"></ion-icon>
              </ion-item-option>
            </ion-item-options>
          </ion-item-sliding>
       </ion-list>
     </div>


    <div v-if="tabActivo === 'categorias'">
        <h2 style="color: white;">Categorías</h2>
        <ion-list>
          <ion-item-sliding v-for="categoria in listaCategorias" :key="categoria.id">
            <ion-item color="primary">
              <ion-label>
                <h2>{{ categoria.nombre }}</h2>
              </ion-label>
            </ion-item>
            
            <ion-item-options side="end">
              <ion-item-option color="tertiary" @click="editarCategoria(categoria)">
                <ion-icon slot="icon-only" :icon="pencil"></ion-icon>
              </ion-item-option>
              <ion-item-option color="danger" @click="eliminarCategoria(categoria.id)">
                <ion-icon slot="icon-only" :icon="trash"></ion-icon>
              </ion-item-option>
            </ion-item-options>
          </ion-item-sliding>
        </ion-list>
     </div>

    <div v-if="tabActivo === 'usuarios'">
        <h2 style="color: white;">Usuarios</h2>
        <ion-list>
          <ion-item-sliding v-for="usuario in listaUsuarios" :key="usuario.id">
            <ion-item color="primary">
              <ion-label>
                <h2>{{ usuario.name }}</h2>
                <p>{{ usuario.email }}</p>
              </ion-label>
            </ion-item>
            <ion-item-options side="end">
              <ion-item-option color="tertiary" @click="editarUsuario(usuario)">
                <ion-icon slot="icon-only" :icon="pencil"></ion-icon>
              </ion-item-option>
              <ion-item-option color="danger" @click="eliminarUsuario(usuario.id)">
                <ion-icon slot="icon-only" :icon="trash"></ion-icon>
              </ion-item-option>
            </ion-item-options>
          </ion-item-sliding>
        </ion-list>
    </div>
    </ion-content>
   
    <ion-footer>
      <ion-toolbar color="primary">
        <ion-segment v-model="tabActivo">
          
          <ion-segment-button value="productos" layout="icon-top">
            <ion-icon :icon="fastFood"></ion-icon>
          </ion-segment-button>
          
          <ion-segment-button value="categorias" layout="icon-top">
            <ion-icon :icon="list"></ion-icon>
          </ion-segment-button>
          
          <ion-segment-button value="usuarios" layout="icon-top">
            <ion-icon :icon="people"></ion-icon>
          </ion-segment-button>
          
        </ion-segment>
      </ion-toolbar>
    </ion-footer>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { fastFood, list, people, add, pencil, trash } from 'ionicons/icons';
import { IonPage, IonContent,IonHeader,IonToolbar,IonTitle,IonButtons,IonIcon,IonText,
    IonButton,IonMenuButton,IonList,IonItem,IonLabel, IonFooter, IonTabBar, IonTabButton,
IonFab, IonFabButton, IonItemSliding, IonItemOptions, IonItemOption, IonSegment, IonSegmentButton} from '@ionic/vue';
import AppHeader from '@/components/AppHeader.vue'; 

const tabActivo = ref('productos');

const listaProductos = ref<any[]>([]);
const listaCategorias = ref<any[]>([]);
const listaUsuarios = ref<any[]>([]);

const cargarProductos = async () => {
    // datos quemados
  listaProductos.value = [
    { id: 1, nombre: 'Hamburguesa Clásica', precio: 4500, estado: 'Disponible' },
    { id: 2, nombre: 'Pizza Margarita', precio: 6000, estado: 'Disponible' },
    { id: 3, nombre: 'Casado con Pollo', precio: 3500, estado: 'Agotado' }
  ];
};

const editarProducto = (producto: any) => {

};

const eliminarProducto = (id: number) => {

  listaProductos.value = listaProductos.value.filter(p => p.id !== id);
};
const cargarCategorias = async () => {
  console.log('Cargando categorías...');
  // Datos quemados
  listaCategorias.value = [
    { id: 1, nombre: 'Entradas' },
    { id: 2, nombre: 'Platos fuertes' },
    { id: 3, nombre: 'Bebidas' },
    { id: 4, nombre: 'Postres' }
  ];
};

const editarCategoria = (categoria: any) => {

};

const eliminarCategoria = (id: number) => {

  listaCategorias.value = listaCategorias.value.filter(c => c.id !== id);
};

const cargarUsuarios = async () => {
    //datos quemados
  listaUsuarios.value = [
    { id: 1, name: 'Admin Demo', email: 'admin@bonappetit.test', rol: 'administrador' },
    { id: 2, name: 'Mesero Demo', email: 'mesero@bonappetit.test', rol: 'mesero' },
    { id: 3, name: 'Cajero Demo', email: 'cajero@bonappetit.test', rol: 'cajero' }
  ];
};

const editarUsuario = (usuario: any) => {

};

const eliminarUsuario = (id: number) => {

  listaUsuarios.value = listaUsuarios.value.filter(u => u.id !== id);
};
const accionAgregar = () => {
  if (tabActivo.value === 'productos') {
    console.log('Abriendo formulario para crear un nuevo PRODUCTO...');
    
  } else if (tabActivo.value === 'categorias') {
    console.log('Abriendo modal para crear una nueva CATEGORÍA...');
    
  } else if (tabActivo.value === 'usuarios') {
    console.log('Abriendo modal para crear un nuevo USUARIO...');
    
  }
};
onMounted(() => {
  cargarProductos();
  cargarCategorias();
  cargarUsuarios();
});
</script>

<style scoped>

</style>