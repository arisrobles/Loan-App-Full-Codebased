import { Slot } from 'expo-router';
import { SocketProvider } from '../src/contexts/SocketContext';

export default function RootLayout() {
  // Use Slot to automatically handle all routes including index
  return (
    <SocketProvider>
      <Slot />
    </SocketProvider>
  );
}


