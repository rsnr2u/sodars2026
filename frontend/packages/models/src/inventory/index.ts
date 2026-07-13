export interface Inventory {
  id: string;
  providerId: string;
  name: string;
  category: 'Digital LED' | 'Static' | 'UniPole' | 'Gantry' | 'Bus Shelter' | 'Metro' | 'Airport' | 'Mall' | 'Cinema' | 'Highway' | 'Wall Wrap' | 'Transit Media' | 'Pole Kiosk' | 'Street Furniture' | 'Digital Totem';
  city: string;
  state: string;
  pricePerMonth: number;
  status: 'Available' | 'Booked' | 'Maintenance';
}
