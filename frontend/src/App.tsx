import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Page } from "./components/Page";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Page>home</Page>} />
      </Routes>
    </BrowserRouter>
  );
}

export default App
