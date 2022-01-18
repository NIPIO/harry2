import React from "react";
import { Redirect } from "react-router-dom";

// Layout Types
import { DefaultLayout } from "./layouts";

// Route Views
import BlogOverview from "./views/BlogOverview";
import UserProfileLite from "./views/UserProfileLite";

import Productos from "./pages/Productos/Productos";
import Marcas from "./pages/Marcas/Marcas";
import Clientes from "./pages/Clientes/Clientes";
import Vendedores from "./pages/Vendedores/Vendedores";
import Proveedores from "./pages/Proveedores/Proveedores";
import Cuentas from "./pages/Cuentas/Cuentas";
import Ventas from "./pages/Ventas/Ventas";
import Compras from "./pages/Compras/Compras";
import Login from "./pages/Login/Login";
import NuevoUsuario from "./pages/Login/NuevoUsuario";

export default [
  {
    path: "/",
    exact: true,
    layout: DefaultLayout,
    component: () => <Redirect to="/ventas" />
  },
  {
    path: "/productos",
    layout: DefaultLayout,
    component: Productos
  },
  {
    path: "/marcas",
    layout: DefaultLayout,
    component: Marcas
  },
  {
    path: "/vendedores",
    layout: DefaultLayout,
    component: Vendedores
  },
  {
    path: "/clientes",
    layout: DefaultLayout,
    component: Clientes
  },
  {
    path: "/ventas",
    layout: DefaultLayout,
    component: Ventas
  },
  {
    path: "/proveedores",
    layout: DefaultLayout,
    component: Proveedores
  },
  {
    path: "/compras",
    layout: DefaultLayout,
    component: Compras
  },
  {
    path: "/cuentas",
    layout: DefaultLayout,
    component: Cuentas
  },
  {
    path: "/login",
    component: Login
  },
  {
    path: "/registrarse",
    component: NuevoUsuario
  },
  {
    path: "/blog-overview",
    layout: DefaultLayout,
    component: BlogOverview
  },
  {
    path: "/user-profile-lite",
    layout: DefaultLayout,
    component: UserProfileLite
  }
];
