"use client";

import { createContext, useContext } from "react";

export interface AdminContextType {
  onOpenMobileSidebar: () => void;
  mobileSidebarOpen: boolean;
  setMobileMenuButton: (button: HTMLButtonElement | null) => void;
}

export const AdminContext = createContext<AdminContextType>({
  onOpenMobileSidebar: () => {},
  mobileSidebarOpen: false,
  setMobileMenuButton: () => {},
});

export const useAdmin = () => useContext(AdminContext);
