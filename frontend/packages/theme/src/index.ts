export interface ThemeColors {
  primary: string;
  secondary: string;
  background: string;
}

export const lightTheme: ThemeColors = {
  primary: '#0B5D4B',
  secondary: '#10B981',
  background: '#F8FAFC',
};

export const darkTheme: ThemeColors = {
  primary: '#10B981',
  secondary: '#0B5D4B',
  background: '#0B0F19',
};

export const highContrastTheme: ThemeColors = {
  primary: '#00FF00',
  secondary: '#FFFFFF',
  background: '#000000',
};
